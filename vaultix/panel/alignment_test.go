// Alignment enforcement for ADR 0005: the contract, the wire quarantine,
// and the canonical namespace schema must not drift apart. These tests read
// the schema files as inputs — change the schema, not the test.
package main

import (
	"encoding/json"
	"os"
	"path/filepath"
	"regexp"
	"strings"
	"testing"
)

const (
	contractPath  = "../contracts/shippin.vaultix.panel.v1.json"
	namespacePath = "../schemas/shippin.vaultix.api.v1.json"
	inventoryPath = "../schemas/upstream.core.v0.162.19.endpoints.tsv"
	wirePath      = "internal/core/wire.go"
)

// Wire paths that are real but absent from the core's OpenAPI (unauthed
// liveness etc.). Keep this list justified, not convenient.
var wireSpecExceptions = map[string]string{
	"/api/status": "liveness route, registered outside the OpenAPI spec",
}

// Brand-token data sites the namespace schema allows (boundaries section).
var brandTokenAllowed = map[string]int{
	"main.go":                    1, // VAULTIX_SOURCE_URL default value
	"internal/httpapi/server.go": 1, // link source default value
}

type namespaceSchema struct {
	Naming struct {
		PublicRoutePattern string `json:"publicRoutePattern"`
		CapabilityPattern  string `json:"capabilityPattern"`
		ContractIDPattern  string `json:"contractIdPattern"`
	} `json:"naming"`
}

type contract struct {
	ServiceID    string   `json:"serviceId"`
	Capabilities []string `json:"capabilities"`
	API          struct {
		Routes []struct {
			Method string `json:"method"`
			Path   string `json:"path"`
		} `json:"routes"`
	} `json:"api"`
}

func loadJSON(t *testing.T, path string, v any) {
	t.Helper()
	raw, err := os.ReadFile(path)
	if err != nil {
		t.Fatalf("read %s: %v", path, err)
	}
	if err := json.Unmarshal(raw, v); err != nil {
		t.Fatalf("parse %s: %v", path, err)
	}
}

func TestContractMatchesNamingCanon(t *testing.T) {
	var ns namespaceSchema
	var c contract
	loadJSON(t, namespacePath, &ns)
	loadJSON(t, contractPath, &c)

	routeRe := regexp.MustCompile(ns.Naming.PublicRoutePattern)
	capRe := regexp.MustCompile(ns.Naming.CapabilityPattern)

	if c.ServiceID != "shippin.vaultix" {
		t.Errorf("serviceId %q", c.ServiceID)
	}
	if len(c.API.Routes) == 0 || len(c.Capabilities) == 0 {
		t.Fatal("contract has no routes or no capabilities — wrong file?")
	}
	for _, r := range c.API.Routes {
		if !routeRe.MatchString(r.Path) {
			t.Errorf("route %s %s violates publicRoutePattern", r.Method, r.Path)
		}
	}
	for _, cap := range c.Capabilities {
		if !capRe.MatchString(cap) {
			t.Errorf("capability %q violates capabilityPattern", cap)
		}
	}
}

func TestWirePathsExistInCoreInventory(t *testing.T) {
	wireSrc, err := os.ReadFile(wirePath)
	if err != nil {
		t.Fatal(err)
	}
	paths := regexp.MustCompile(`"(/api[^"]*)"`).FindAllStringSubmatch(string(wireSrc), -1)
	if len(paths) == 0 {
		t.Fatal("no wire paths found — wire.go moved?")
	}

	inv, err := os.ReadFile(inventoryPath)
	if err != nil {
		t.Fatal(err)
	}
	corePaths := []string{}
	eePaths := map[string]bool{}
	for _, line := range strings.Split(string(inv), "\n")[1:] {
		f := strings.Split(line, "\t")
		if len(f) < 3 {
			continue
		}
		if f[2] == "core" {
			corePaths = append(corePaths, f[1])
		} else {
			eePaths[f[1]] = true
		}
	}

	for _, m := range paths {
		wp := strings.TrimSuffix(m[1], "/")
		if _, ok := wireSpecExceptions[wp]; ok {
			continue
		}
		found := false
		for _, cp := range corePaths {
			if cp == wp || strings.HasPrefix(cp, wp+"/") || strings.HasPrefix(cp, wp+"?") {
				found = true
				break
			}
		}
		if !found {
			if eePaths[wp] {
				t.Errorf("wire path %s resolves to an ee/ endpoint — refused tree (doc 0001/0003)", wp)
			} else {
				t.Errorf("wire path %s not found in core inventory %s", wp, inventoryPath)
			}
		}
	}
}

func TestBrandTokenQuarantine(t *testing.T) {
	re := regexp.MustCompile(`(?i)infisical`)
	err := filepath.WalkDir(".", func(path string, d os.DirEntry, err error) error {
		if err != nil || d.IsDir() || !strings.HasSuffix(path, ".go") || strings.HasSuffix(path, "_test.go") {
			return err
		}
		raw, err := os.ReadFile(path)
		if err != nil {
			return err
		}
		n := len(re.FindAll(raw, -1))
		if allowed, ok := brandTokenAllowed[filepath.ToSlash(path)]; ok {
			if n > allowed {
				t.Errorf("%s: %d brand tokens, schema allows %d", path, n, allowed)
			}
			return nil
		}
		if n > 0 {
			t.Errorf("%s: brand token outside allowlist (%d occurrences)", path, n)
		}
		return nil
	})
	if err != nil {
		t.Fatal(err)
	}

	// Contract routes/capabilities must be brand-free (provenance block in
	// source.upstream is licensing fact and exempt).
	var c contract
	loadJSON(t, contractPath, &c)
	for _, r := range c.API.Routes {
		if re.MatchString(r.Path) {
			t.Errorf("contract route %s carries a brand token", r.Path)
		}
	}
	for _, cap := range c.Capabilities {
		if re.MatchString(cap) {
			t.Errorf("capability %s carries a brand token", cap)
		}
	}
}
