package httpapi

import (
	"bytes"
	"encoding/json"
	"fmt"
	"net/http"
	"net/http/httptest"
	"os"
	"path/filepath"
	"strings"
	"sync"
	"testing"

	"shippin.cloud/vaultix/panel/internal/audit"
	"shippin.cloud/vaultix/panel/internal/instances"
	"shippin.cloud/vaultix/panel/internal/pinauth"
	"shippin.cloud/vaultix/panel/internal/store"
)

const testKey = "000102030405060708090a0b0c0d0e0f000102030405060708090a0b0c0d0e0f"

// fakeCore implements the verified wire surface of the pinned core
// (doc 0006): universal-auth login (flat response), /api/v1/projects
// ({project} envelope), /api/v4/secrets list/single/batch (string-enum
// bools, union write responses), /api/status.
type fakeCore struct {
	mu         sync.Mutex
	clientID   string
	envs       []string
	secrets    map[string]map[string]string // env -> key -> value
	written    int
	hideValues bool // serve secretValueHidden=true placeholders
	holdWrites bool // respond with the {approval:...} union branch
}

func (f *fakeCore) handler() http.Handler {
	mux := http.NewServeMux()
	mux.HandleFunc("POST /api/v1/auth/universal-auth/login", func(w http.ResponseWriter, r *http.Request) {
		var in struct{ ClientId, ClientSecret string }
		json.NewDecoder(r.Body).Decode(&in)
		if in.ClientId != f.clientID {
			http.Error(w, `{"message":"bad credential"}`, http.StatusUnauthorized)
			return
		}
		json.NewEncoder(w).Encode(map[string]any{
			"accessToken": "tok-" + f.clientID, "expiresIn": 3600,
			"accessTokenMaxTTL": 2592000, "tokenType": "Bearer",
		})
	})
	mux.HandleFunc("GET /api/v1/projects/{id}", func(w http.ResponseWriter, r *http.Request) {
		if !f.authed(r) {
			http.Error(w, "no auth", http.StatusUnauthorized)
			return
		}
		type env struct {
			ID   string `json:"id"`
			Name string `json:"name"`
			Slug string `json:"slug"`
		}
		envs := []env{}
		for i, e := range f.envs {
			envs = append(envs, env{fmt.Sprintf("env-%d", i), strings.ToUpper(e), e})
		}
		json.NewEncoder(w).Encode(map[string]any{"project": map[string]any{
			"id": r.PathValue("id"), "name": "proj", "environments": envs,
		}})
	})
	mux.HandleFunc("POST /api/v1/projects", func(w http.ResponseWriter, r *http.Request) {
		if !f.authed(r) {
			http.Error(w, "no auth", http.StatusUnauthorized)
			return
		}
		var in struct {
			ProjectName string `json:"projectName"`
		}
		json.NewDecoder(r.Body).Decode(&in)
		json.NewEncoder(w).Encode(map[string]any{"project": map[string]any{
			"id": "proj-" + in.ProjectName, "name": in.ProjectName,
		}})
	})
	mux.HandleFunc("GET /api/v4/secrets", func(w http.ResponseWriter, r *http.Request) {
		if !f.authed(r) {
			http.Error(w, "no auth", http.StatusUnauthorized)
			return
		}
		if r.URL.Query().Get("recursive") != "true" {
			// bool params are string enums upstream; anything else is a 400
			http.Error(w, `{"message":"bad recursive"}`, http.StatusBadRequest)
			return
		}
		f.mu.Lock()
		defer f.mu.Unlock()
		type sec struct {
			SecretKey         string `json:"secretKey"`
			SecretValue       string `json:"secretValue"`
			SecretPath        string `json:"secretPath"`
			SecretValueHidden bool   `json:"secretValueHidden"`
		}
		out := []sec{}
		for k, v := range f.secrets[r.URL.Query().Get("environment")] {
			if f.hideValues {
				out = append(out, sec{k, "<hidden-by-core>", "/", true})
			} else {
				out = append(out, sec{k, v, "/", false})
			}
		}
		json.NewEncoder(w).Encode(map[string]any{"secrets": out})
	})
	batch := func(w http.ResponseWriter, r *http.Request) {
		if !f.authed(r) {
			http.Error(w, "no auth", http.StatusUnauthorized)
			return
		}
		if f.holdWrites {
			json.NewEncoder(w).Encode(map[string]any{"approval": map[string]any{"id": "appr-1"}})
			return
		}
		var in struct {
			Environment string `json:"environment"`
			Secrets     []struct {
				SecretKey   string `json:"secretKey"`
				SecretValue string `json:"secretValue"`
			} `json:"secrets"`
		}
		json.NewDecoder(r.Body).Decode(&in)
		f.mu.Lock()
		defer f.mu.Unlock()
		f.store(in.Environment, func(env map[string]string) {
			for _, s := range in.Secrets {
				env[s.SecretKey] = s.SecretValue
				f.written++
			}
		})
		json.NewEncoder(w).Encode(map[string]any{"secrets": []any{}})
	}
	mux.HandleFunc("POST /api/v4/secrets/batch", batch)
	mux.HandleFunc("PATCH /api/v4/secrets/batch", batch)
	mux.HandleFunc("POST /api/v4/secrets/{name}", func(w http.ResponseWriter, r *http.Request) {
		if !f.authed(r) {
			http.Error(w, "no auth", http.StatusUnauthorized)
			return
		}
		if f.holdWrites {
			json.NewEncoder(w).Encode(map[string]any{"approval": map[string]any{"id": "appr-1"}})
			return
		}
		var in struct {
			Environment string `json:"environment"`
			SecretValue string `json:"secretValue"`
		}
		json.NewDecoder(r.Body).Decode(&in)
		f.mu.Lock()
		defer f.mu.Unlock()
		f.store(in.Environment, func(env map[string]string) {
			env[r.PathValue("name")] = in.SecretValue
			f.written++
		})
		json.NewEncoder(w).Encode(map[string]any{"secret": map[string]any{}})
	})
	mux.HandleFunc("GET /api/status", func(w http.ResponseWriter, _ *http.Request) {
		w.WriteHeader(http.StatusOK)
	})
	return mux
}

func (f *fakeCore) store(env string, fn func(map[string]string)) {
	if f.secrets == nil {
		f.secrets = map[string]map[string]string{}
	}
	if f.secrets[env] == nil {
		f.secrets[env] = map[string]string{}
	}
	fn(f.secrets[env])
}

func (f *fakeCore) authed(r *http.Request) bool {
	return r.Header.Get("Authorization") == "Bearer tok-"+f.clientID
}

type fixture struct {
	srv    *Server
	ts     *httptest.Server
	source *fakeCore
	local  *fakeCore
}

func newFixture(t *testing.T) *fixture {
	t.Helper()
	source := &fakeCore{clientID: "source-id", envs: []string{"dev", "prod"},
		secrets: map[string]map[string]string{
			"dev":  {"OPENAI_API_KEY": "sk-dev"},
			"prod": {"OPENAI_API_KEY": "sk-prod", "DB_URL": "postgres://x"},
		}}
	local := &fakeCore{clientID: "local-id", envs: []string{"dev", "prod"}}
	sourceTS := httptest.NewServer(source.handler())
	localTS := httptest.NewServer(local.handler())
	t.Cleanup(sourceTS.Close)
	t.Cleanup(localTS.Close)

	regPath := filepath.Join(t.TempDir(), "instances.json")
	reg := fmt.Sprintf(`[{"id":"vaultix-prod","name":"Vaultix (Zuul host)","host":"192.227.210.218","url":%q}]`, localTS.URL)
	if err := os.WriteFile(regPath, []byte(reg), 0o600); err != nil {
		t.Fatal(err)
	}
	registry, err := instances.Load(regPath)
	if err != nil {
		t.Fatal(err)
	}
	st, err := store.Open(filepath.Join(t.TempDir(), "state.json"), testKey)
	if err != nil {
		t.Fatal(err)
	}
	srv := &Server{
		Store: st, Pins: pinauth.New(st), Registry: registry,
		Audit:          audit.New(""),
		IdentityHeader: "X-Authentik-Username",
		SourceURL:      sourceTS.URL,
		Local:          LocalConfig{URL: localTS.URL, ClientID: "local-id", ClientSecret: "s"},
	}
	ts := httptest.NewServer(srv.Handler())
	t.Cleanup(ts.Close)
	return &fixture{srv: srv, ts: ts, source: source, local: local}
}

func (f *fixture) call(t *testing.T, method, path, user, elevated string, body any) (int, map[string]any) {
	t.Helper()
	var rd *bytes.Reader
	if body != nil {
		raw, _ := json.Marshal(body)
		rd = bytes.NewReader(raw)
	} else {
		rd = bytes.NewReader(nil)
	}
	req, err := http.NewRequest(method, f.ts.URL+path, rd)
	if err != nil {
		t.Fatal(err)
	}
	if user != "" {
		req.Header.Set("X-Authentik-Username", user)
	}
	if elevated != "" {
		req.Header.Set("X-Vaultix-Elevated", elevated)
	}
	resp, err := http.DefaultClient.Do(req)
	if err != nil {
		t.Fatal(err)
	}
	defer resp.Body.Close()
	var out map[string]any
	json.NewDecoder(resp.Body).Decode(&out)
	return resp.StatusCode, out
}

func (f *fixture) elevate(t *testing.T, user string) string {
	t.Helper()
	code, out := f.call(t, "POST", "/api/v1/vaultix/session/pin", user, "",
		map[string]string{"instanceId": "vaultix-prod", "newPin": "482913"})
	if code != http.StatusOK {
		t.Fatalf("set pin: %d %v", code, out)
	}
	code, out = f.call(t, "POST", "/api/v1/vaultix/session/stepup", user, "",
		map[string]string{"instanceId": "vaultix-prod", "pin": "482913"})
	if code != http.StatusOK {
		t.Fatalf("stepup: %d %v", code, out)
	}
	return out["token"].(string)
}

func (f *fixture) link(t *testing.T, tok string) {
	t.Helper()
	code, out := f.call(t, "POST", "/api/v1/vaultix/link", "jessay", tok, map[string]any{
		"clientId": "source-id", "clientSecret": "x",
		"projects": []map[string]string{{"sourceProjectId": "cw1", "localProjectId": "lw1"}},
	})
	if code != http.StatusCreated {
		t.Fatalf("link: %d %v", code, out)
	}
}

func TestIdentityRequired(t *testing.T) {
	f := newFixture(t)
	code, _ := f.call(t, "GET", "/api/v1/vaultix/instances", "", "", nil)
	if code != http.StatusUnauthorized {
		t.Fatalf("want 401, got %d", code)
	}
}

func TestInstancesMetadataOnly(t *testing.T) {
	f := newFixture(t)
	code, out := f.call(t, "GET", "/api/v1/vaultix/instances", "jessay", "", nil)
	if code != http.StatusOK {
		t.Fatalf("want 200, got %d", code)
	}
	items := out["instances"].([]any)
	if len(items) != 1 {
		t.Fatalf("want 1 instance, got %d", len(items))
	}
	inst := items[0].(map[string]any)
	if inst["health"] != "ok" {
		t.Fatalf("health probe failed: %v", inst)
	}
	raw, _ := json.Marshal(out)
	if strings.Contains(string(raw), "sk-") || strings.Contains(string(raw), "OPENAI") {
		t.Fatal("instance list leaked secret material")
	}
}

func TestElevationGate(t *testing.T) {
	f := newFixture(t)
	code, _ := f.call(t, "POST", "/api/v1/vaultix/inject", "jessay", "",
		map[string]string{"projectId": "p", "environment": "dev"})
	if code != http.StatusForbidden {
		t.Fatalf("want 403 without step-up, got %d", code)
	}
	tok := f.elevate(t, "jessay")
	code, _ = f.call(t, "POST", "/api/v1/vaultix/inject", "mallory", tok,
		map[string]string{"projectId": "p", "environment": "dev"})
	if code != http.StatusForbidden {
		t.Fatalf("want 403 for stolen token, got %d", code)
	}
	code, out := f.call(t, "POST", "/api/v1/vaultix/inject", "jessay", tok,
		map[string]string{"projectId": "p", "environment": "dev"})
	if code != http.StatusCreated || out["handle"] == "" {
		t.Fatalf("inject: %d %v", code, out)
	}
}

func TestLinkAndImport(t *testing.T) {
	f := newFixture(t)
	tok := f.elevate(t, "jessay")

	code, _ := f.call(t, "POST", "/api/v1/vaultix/link", "jessay", tok, map[string]any{
		"clientId": "wrong", "clientSecret": "x",
	})
	if code != http.StatusBadGateway {
		t.Fatalf("bad cred should 502, got %d", code)
	}

	f.link(t, tok)
	code, out := f.call(t, "GET", "/api/v1/vaultix/link", "jessay", "", nil)
	if code != http.StatusOK || out["linked"] != true {
		t.Fatalf("get link: %d %v", code, out)
	}
	raw, _ := json.Marshal(out)
	if strings.Contains(string(raw), "source-id") {
		t.Fatal("link GET leaked credential")
	}

	code, out = f.call(t, "POST", "/api/v1/vaultix/import", "jessay", tok, nil)
	if code != http.StatusOK {
		t.Fatalf("import: %d %v", code, out)
	}
	if f.local.written != 3 {
		t.Fatalf("want 3 secrets written locally, got %d", f.local.written)
	}
	if f.local.secrets["prod"]["DB_URL"] != "postgres://x" {
		t.Fatal("secret value did not arrive in local instance")
	}
	if f.source.written != 0 {
		t.Fatalf("import must never write to source side, wrote %d", f.source.written)
	}

	// Re-run: idempotent, updates instead of duplicating.
	code, _ = f.call(t, "POST", "/api/v1/vaultix/import", "jessay", tok, nil)
	if code != http.StatusOK {
		t.Fatalf("re-import: %d", code)
	}
	if len(f.local.secrets["prod"]) != 2 {
		t.Fatalf("re-import duplicated secrets: %v", f.local.secrets["prod"])
	}

	code, out = f.call(t, "DELETE", "/api/v1/vaultix/link", "jessay", tok, nil)
	if code != http.StatusOK || out["linked"] != false {
		t.Fatalf("unlink: %d %v", code, out)
	}
}

// A link identity without value-read permission gets placeholder values
// (secretValueHidden). Importing those would corrupt the target — the env
// must fail and nothing may be written.
func TestImportRefusesHiddenValues(t *testing.T) {
	f := newFixture(t)
	f.source.hideValues = true
	tok := f.elevate(t, "jessay")
	f.link(t, tok)

	code, out := f.call(t, "POST", "/api/v1/vaultix/import", "jessay", tok, nil)
	if code != http.StatusOK {
		t.Fatalf("import: %d %v", code, out)
	}
	if f.local.written != 0 {
		t.Fatalf("placeholder values were written: %d", f.local.written)
	}
	raw, _ := json.Marshal(out)
	if !strings.Contains(string(raw), "hidden") {
		t.Fatalf("report must name the hidden-value refusal: %s", raw)
	}
}

// A target with a secret-protection (approval) policy answers writes with
// HTTP 200 + {approval}. That is a held write, not a success.
func TestImportSurfacesApprovalHold(t *testing.T) {
	f := newFixture(t)
	f.local.holdWrites = true
	tok := f.elevate(t, "jessay")
	f.link(t, tok)

	code, out := f.call(t, "POST", "/api/v1/vaultix/import", "jessay", tok, nil)
	if code != http.StatusOK {
		t.Fatalf("import: %d %v", code, out)
	}
	if f.local.written != 0 {
		t.Fatal("approval-held write counted as written")
	}
	raw, _ := json.Marshal(out)
	if !strings.Contains(string(raw), "approval") {
		t.Fatalf("report must surface the approval hold: %s", raw)
	}
}

func TestSecretPutMetadataOnly(t *testing.T) {
	f := newFixture(t)
	tok := f.elevate(t, "jessay")
	code, out := f.call(t, "PUT", "/api/v1/vaultix/projects/lw1/secrets/API_KEY", "jessay", tok,
		map[string]string{"environment": "dev", "value": "super-secret-value"})
	if code != http.StatusOK {
		t.Fatalf("put: %d %v", code, out)
	}
	raw, _ := json.Marshal(out)
	if strings.Contains(string(raw), "super-secret-value") {
		t.Fatal("secret put echoed the value back")
	}
	if f.local.secrets["dev"]["API_KEY"] != "super-secret-value" {
		t.Fatal("value did not land in local instance")
	}
}
