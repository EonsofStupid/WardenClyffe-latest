// Intelligence is the cortex-intelligence catalog: a direct, READ-ONLY v1
// connection to the self-hosted intelligence plane — SurrealDB (graph) and
// Qdrant (retrieval). Mutations stay with the sync worker; this surface only
// queries. Credentials resolve via platform.Secret (env / brokered tmpfs),
// never from package files.
package catalog

import (
	"bytes"
	"encoding/json"
	"errors"
	"io"
	"net/http"
	"strings"
	"time"

	"github.com/wardenclyffe/cortex-mcp/internal/platform"
	"github.com/wardenclyffe/cortex-mcp/internal/protocol"
)

// ErrMutationBlocked rejects non-read SurrealQL in v1.
var ErrMutationBlocked = errors.New("mutation blocked: cortex-intelligence v1 is read-only (SELECT/INFO); writes go through the sync worker")

// Intelligence implements protocol.Catalog for the intelligence layer.
type Intelligence struct {
	repo platform.Config
	cfg  platform.IntelligenceConfig
	http *http.Client
}

// NewIntelligence returns the intelligence catalog.
func NewIntelligence(repo platform.Config, cfg platform.IntelligenceConfig) *Intelligence {
	return &Intelligence{repo: repo, cfg: cfg, http: &http.Client{Timeout: 8 * time.Second}}
}

// ListTools returns the surreal.* and qdrant.* tool definitions.
func (g *Intelligence) ListTools() []protocol.ToolDef {
	return []protocol.ToolDef{
		{Name: "surreal.query",
			Description: "Run a READ-ONLY SurrealQL statement (SELECT/INFO) against the self-hosted SurrealDB graph projection.",
			InputSchema: schema(`{"type":"object","properties":{"sql":{"type":"string","minLength":3}},"required":["sql"],"additionalProperties":false}`)},
		{Name: "surreal.list_tables",
			Description: "List tables in the configured SurrealDB namespace/database (INFO FOR DB).",
			InputSchema: schema(`{"type":"object","properties":{},"additionalProperties":false}`)},
		{Name: "qdrant.list_collections",
			Description: "List Qdrant collections on the self-hosted retrieval plane.",
			InputSchema: schema(`{"type":"object","properties":{},"additionalProperties":false}`)},
		{Name: "qdrant.collection_info",
			Description: "Get one Qdrant collection's status, size, and vector config.",
			InputSchema: schema(`{"type":"object","properties":{"collection":{"type":"string","minLength":1}},"required":["collection"],"additionalProperties":false}`)},
		{Name: "qdrant.scroll",
			Description: "Read points (payloads) from a Qdrant collection without a query vector — paged browse of the retrieval store.",
			InputSchema: schema(`{"type":"object","properties":{"collection":{"type":"string","minLength":1},"limit":{"type":"integer","minimum":1,"maximum":100,"default":10}},"required":["collection"],"additionalProperties":false}`)},
	}
}

// CallTool dispatches one intelligence tool call.
func (g *Intelligence) CallTool(name string, args json.RawMessage) (string, bool) {
	switch name {
	case "surreal.query":
		var in struct {
			SQL string `json:"sql"`
		}
		if err := json.Unmarshal(args, &in); err != nil || strings.TrimSpace(in.SQL) == "" {
			return "sql is required", true
		}
		return g.surrealQuery(in.SQL)
	case "surreal.list_tables":
		return g.surrealQuery("INFO FOR DB;")
	case "qdrant.list_collections":
		return g.qdrantGet("/collections")
	case "qdrant.collection_info":
		var in struct {
			Collection string `json:"collection"`
		}
		if err := json.Unmarshal(args, &in); err != nil || in.Collection == "" {
			return "collection is required", true
		}
		return g.qdrantGet("/collections/" + in.Collection)
	case "qdrant.scroll":
		var in struct {
			Collection string `json:"collection"`
			Limit      int    `json:"limit"`
		}
		if err := json.Unmarshal(args, &in); err != nil || in.Collection == "" {
			return "collection is required", true
		}
		if in.Limit <= 0 || in.Limit > 100 {
			in.Limit = 10
		}
		body, _ := json.Marshal(map[string]any{"limit": in.Limit, "with_payload": true})
		return g.qdrantPost("/collections/"+in.Collection+"/points/scroll", body)
	default:
		return "unknown tool: " + name, true
	}
}

// ListResources routes to the intelligence-layer contract docs.
func (g *Intelligence) ListResources() []protocol.ResourceDef {
	return []protocol.ResourceDef{
		{URI: "cortex://intelligence/contract", Name: "Intelligence layer contract",
			Description: "Markdown routes, stores own memory: the controlling split.", MimeType: "text/markdown"},
		{URI: "cortex://intelligence/projection", Name: "SurrealDB projection plan",
			Description: "Touchpoint -> graph projection model (v2).", MimeType: "text/markdown"},
		{URI: "cortex://intelligence/sync-pattern", Name: "Touchpoint sync pattern",
			Description: "The authored->projected pipeline and payload shapes.", MimeType: "text/markdown"},
	}
}

// ReadResource resolves the intelligence contract docs.
func (g *Intelligence) ReadResource(uri string) (string, error) {
	m := map[string]string{
		"cortex://intelligence/contract":     "docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md",
		"cortex://intelligence/projection":   "docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md",
		"cortex://intelligence/sync-pattern": "docs/ai/TOUCHPOINT_SYNC_PATTERN.md",
	}
	rel, ok := m[uri]
	if !ok {
		return "", ErrUnknownResource
	}
	return readRepoFile(g.repo.RepoRoot, rel)
}

// ListPrompts — the intelligence catalog carries no strict processes in v1.
func (g *Intelligence) ListPrompts() []protocol.PromptDef { return []protocol.PromptDef{} }

// GetPrompt — none registered in v1.
func (g *Intelligence) GetPrompt(string, map[string]string) (string, error) {
	return "", ErrUnknownPrompt
}

// surrealQuery posts read-only SurrealQL to the HTTP /sql endpoint.
func (g *Intelligence) surrealQuery(sql string) (string, bool) {
	if err := guardReadOnly(sql); err != nil {
		return err.Error(), true
	}
	req, err := http.NewRequest(http.MethodPost, g.cfg.SurrealURL+"/sql", bytes.NewBufferString(sql))
	if err != nil {
		return err.Error(), true
	}
	req.Header.Set("Accept", "application/json")
	// v2/v3 header names plus legacy fallbacks (see surrealdb cheatsheet).
	req.Header.Set("Surreal-NS", g.cfg.SurrealNS)
	req.Header.Set("Surreal-DB", g.cfg.SurrealDB)
	req.Header.Set("NS", g.cfg.SurrealNS)
	req.Header.Set("DB", g.cfg.SurrealDB)
	user, pass := platform.Secret("CORTEX_SURREAL_USER"), platform.Secret("CORTEX_SURREAL_PASS")
	if user != "" {
		req.SetBasicAuth(user, pass)
	}
	return g.do(req)
}

func (g *Intelligence) qdrantGet(path string) (string, bool) {
	req, err := http.NewRequest(http.MethodGet, g.cfg.QdrantURL+path, nil)
	if err != nil {
		return err.Error(), true
	}
	g.qdrantAuth(req)
	return g.do(req)
}

func (g *Intelligence) qdrantPost(path string, body []byte) (string, bool) {
	req, err := http.NewRequest(http.MethodPost, g.cfg.QdrantURL+path, bytes.NewReader(body))
	if err != nil {
		return err.Error(), true
	}
	req.Header.Set("Content-Type", "application/json")
	g.qdrantAuth(req)
	return g.do(req)
}

func (g *Intelligence) qdrantAuth(req *http.Request) {
	if key := platform.Secret("CORTEX_QDRANT_API_KEY"); key != "" {
		req.Header.Set("api-key", key)
	}
}

func (g *Intelligence) do(req *http.Request) (string, bool) {
	resp, err := g.http.Do(req)
	if err != nil {
		return "intelligence plane unreachable: " + err.Error(), true
	}
	defer resp.Body.Close()
	b, _ := io.ReadAll(io.LimitReader(resp.Body, 1<<20))
	if resp.StatusCode == http.StatusUnauthorized || resp.StatusCode == http.StatusForbidden {
		return "credential-gated (" + resp.Status + "): broker access via Infisical refs CORTEX_SURREAL_USER/PASS or CORTEX_QDRANT_API_KEY", true
	}
	if resp.StatusCode >= 400 {
		return resp.Status + ": " + string(b), true
	}
	return string(b), false
}

// guardReadOnly rejects mutating SurrealQL verbs in v1.
func guardReadOnly(sql string) error {
	up := " " + strings.ToUpper(sql) + " "
	for _, kw := range []string{"CREATE", "UPDATE", "DELETE", "REMOVE", "DEFINE", "INSERT", "RELATE", "UPSERT", "ALTER", "KILL"} {
		if strings.Contains(up, " "+kw+" ") || strings.HasPrefix(strings.TrimSpace(strings.ToUpper(sql)), kw) {
			return ErrMutationBlocked
		}
	}
	return nil
}
