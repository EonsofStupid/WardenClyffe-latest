package httpapi

import (
	"net/http"
	"testing"

	"shippin.cloud/vaultix/panel/internal/valves"
)

func TestCapabilitiesServedAllClosedByDefault(t *testing.T) {
	f := newFixture(t)
	code, out := f.call(t, "GET", "/api/v1/vaultix/capabilities", "jessay", "", nil)
	if code != http.StatusOK {
		t.Fatalf("capabilities: %d %v", code, out)
	}
	vs, ok := out["valves"].([]any)
	if !ok || len(vs) == 0 {
		t.Fatalf("expected valve list: %v", out)
	}
	for _, v := range vs {
		if v.(map[string]any)["enabled"] == true {
			t.Fatalf("valve open by default: %v", v)
		}
	}
}

func TestApprovalGateClosedAppliesImmediately(t *testing.T) {
	f := newFixture(t)
	tok := f.elevate(t, "jessay")
	code, out := f.call(t, "PUT", "/api/v1/vaultix/projects/lw1/secrets/API_KEY", "jessay", tok,
		map[string]string{"environment": "dev", "value": "v"})
	if code != http.StatusOK {
		t.Fatalf("closed gate must apply immediately: %d %v", code, out)
	}
}

func TestApprovalGateOpenHoldsWrite(t *testing.T) {
	f := newFixture(t)
	f.srv.Valves.ApprovalGate = valves.ApprovalGate{Enabled: true}
	tok := f.elevate(t, "jessay")
	code, out := f.call(t, "PUT", "/api/v1/vaultix/projects/lw1/secrets/API_KEY", "jessay", tok,
		map[string]string{"environment": "dev", "value": "v"})
	if code != http.StatusAccepted || out["held"] != true {
		t.Fatalf("open gate must hold the write: %d %v", code, out)
	}
	// The value must NOT have reached the core.
	if f.local.secrets["dev"]["API_KEY"] == "v" {
		t.Fatal("held write should not have applied")
	}
}

func TestAccessPolicyOpenDeniesPath(t *testing.T) {
	f := newFixture(t)
	f.srv.Valves.AccessPolicy = valves.AccessPolicy{Enabled: true, Deny: []string{"/prod"}}
	tok := f.elevate(t, "jessay")
	code, _ := f.call(t, "PUT", "/api/v1/vaultix/projects/lw1/secrets/K", "jessay", tok,
		map[string]string{"environment": "prod", "value": "v", "path": "/prod"})
	if code != http.StatusForbidden {
		t.Fatalf("denied path must 403, got %d", code)
	}
}

func TestRateCapOpenGates(t *testing.T) {
	f := newFixture(t)
	f.srv.RateCap = valves.NewRateCap(true, 1)
	tok := f.elevate(t, "jessay")
	// first elevated action passes
	c1, _ := f.call(t, "POST", "/api/v1/vaultix/inject", "jessay", tok,
		map[string]string{"projectId": "p", "environment": "dev"})
	if c1 != http.StatusCreated {
		t.Fatalf("first action should pass: %d", c1)
	}
	// second within the minute is capped
	c2, _ := f.call(t, "POST", "/api/v1/vaultix/inject", "jessay", tok,
		map[string]string{"projectId": "p", "environment": "dev"})
	if c2 != http.StatusTooManyRequests {
		t.Fatalf("second action should be capped, got %d", c2)
	}
}
