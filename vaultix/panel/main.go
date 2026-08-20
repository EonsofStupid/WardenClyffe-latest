// vaultix-panel — the shippin.vaultix.panel.v1 adapter.
// Binds loopback only; Authentik forward auth and TLS live in Caddy.
package main

import (
	"log"
	"net/http"
	"os"
	"strconv"
	"strings"

	"shippin.cloud/vaultix/panel/internal/audit"
	"shippin.cloud/vaultix/panel/internal/httpapi"
	"shippin.cloud/vaultix/panel/internal/instances"
	"shippin.cloud/vaultix/panel/internal/pinauth"
	"shippin.cloud/vaultix/panel/internal/store"
	"shippin.cloud/vaultix/panel/internal/usage"
	"shippin.cloud/vaultix/panel/internal/valves"
)

func env(key, def string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return def
}

func splitCSV(s string) []string {
	if s == "" {
		return nil
	}
	parts := strings.Split(s, ",")
	out := parts[:0]
	for _, p := range parts {
		if p = strings.TrimSpace(p); p != "" {
			out = append(out, p)
		}
	}
	return out
}

func atoiOr0(s string) int {
	n, _ := strconv.Atoi(strings.TrimSpace(s))
	return n
}

func main() {
	listen := env("VAULTIX_PANEL_LISTEN", "127.0.0.1:8201")
	statePath := env("VAULTIX_PANEL_STATE", "/var/lib/vaultix-panel/state.json")
	registryPath := env("VAULTIX_PANEL_REGISTRY", "/etc/vaultix-panel/instances.json")
	auditPath := env("VAULTIX_PANEL_AUDIT", "/var/lib/vaultix-panel/audit.jsonl")

	encKey := os.Getenv("VAULTIX_PANEL_ENC_KEY")
	if encKey == "" {
		log.Fatal("VAULTIX_PANEL_ENC_KEY required (64 hex chars; escrow it with the instance keys)")
	}
	st, err := store.Open(statePath, encKey)
	if err != nil {
		log.Fatalf("store: %v", err)
	}
	reg, err := instances.Load(registryPath)
	if err != nil {
		log.Fatalf("registry: %v", err)
	}

	srv := &httpapi.Server{
		Store:          st,
		Pins:           pinauth.New(st),
		Registry:       reg,
		Audit:          audit.New(auditPath),
		IdentityHeader: env("VAULTIX_PANEL_IDENTITY_HEADER", "X-Authentik-Username"),
		SourceURL:      env("VAULTIX_SOURCE_URL", "https://app.infisical.com"),
		Local: httpapi.LocalConfig{
			URL:          os.Getenv("VAULTIX_LOCAL_URL"),
			ClientID:     os.Getenv("VAULTIX_LOCAL_CLIENT_ID"),
			ClientSecret: os.Getenv("VAULTIX_LOCAL_CLIENT_SECRET"),
		},
		// Plan meter (doc 0010): unlimited + unenforced by default. The valve
		// is VAULTIX_METER_ENFORCE=true; limits would be configured alongside.
		Meter: usage.Meter{
			Plan:    usage.Unlimited(),
			Enforce: os.Getenv("VAULTIX_METER_ENFORCE") == "true",
		},
		// Dormant capability valves (doc 0003). All closed by default; each
		// opens with its own env flag. The plumbing exists so enabling any of
		// them later is a flag, not a retrofit.
		Valves: valves.Set{
			IPAllowlist:  valves.NewIPAllowlist(os.Getenv("VAULTIX_IP_ALLOWLIST") != "", splitCSV(os.Getenv("VAULTIX_IP_ALLOWLIST"))),
			ApprovalGate: valves.ApprovalGate{Enabled: os.Getenv("VAULTIX_APPROVAL_GATE") == "true"},
			AuditStream:  valves.AuditStream{Enabled: os.Getenv("VAULTIX_AUDIT_STREAM_URL") != "", Endpoint: os.Getenv("VAULTIX_AUDIT_STREAM_URL")},
			AccessPolicy: valves.AccessPolicy{Enabled: os.Getenv("VAULTIX_ACCESS_POLICY_DENY") != "", Deny: splitCSV(os.Getenv("VAULTIX_ACCESS_POLICY_DENY"))},
		},
		RateCap: valves.NewRateCap(os.Getenv("VAULTIX_RATE_CAP_PER_MIN") != "", atoiOr0(os.Getenv("VAULTIX_RATE_CAP_PER_MIN"))),
	}
	if u := os.Getenv("VAULTIX_AUDIT_STREAM_URL"); u != "" {
		srv.Audit = srv.Audit.WithStream(u)
	}

	log.Printf("vaultix-panel listening on %s (contract shippin.vaultix.panel.v1)", listen)
	log.Fatal(http.ListenAndServe(listen, srv.Handler()))
}
