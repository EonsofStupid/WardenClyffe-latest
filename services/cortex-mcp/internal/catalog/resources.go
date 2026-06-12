package catalog

import (
	"errors"
	"os"
	"path/filepath"
	"sort"
	"strings"

	"github.com/wardenclyffe/cortex-mcp/internal/protocol"
)

// ErrUnknownResource means the cortex:// URI does not resolve.
var ErrUnknownResource = errors.New("unknown resource uri")

// resourceDefs lists the intelligence-layer routing surfaces. Decision
// touchpoints are listed dynamically, one resource per project_key.
func (c *Catalog) resourceDefs() []protocol.ResourceDef {
	defs := []protocol.ResourceDef{
		{URI: "cortex://registry/context-mesh", Name: "Context Mesh registry",
			Description: "The MCP registry source of truth (context-mesh.yaml).", MimeType: "text/yaml"},
		{URI: "cortex://schemas/clyffy-touchpoint.v2", Name: "Touchpoint v2 schema",
			Description: "JSON Schema every v2 clyffy_touchpoint must satisfy.", MimeType: "application/json"},
		{URI: "cortex://docs/naming", Name: "Naming conventions (law)",
			Description: "Products, dirs, vars, functions, triggers — the naming law.", MimeType: "text/markdown"},
		{URI: "cortex://docs/structure", Name: "Structure standard (law)",
			Description: "Colocation law: Go shapes, React shapes, domains, landing convention.", MimeType: "text/markdown"},
	}
	keys, _ := decisionKeys(c.cfg.DecisionsDir)
	for _, k := range keys {
		defs = append(defs, protocol.ResourceDef{
			URI:         "cortex://decisions/" + k,
			Name:        "Decisions: " + k,
			Description: "Locked decision touchpoint (projected to SurrealDB + Qdrant).",
			MimeType:    "text/markdown",
		})
	}
	return defs
}

func (c *Catalog) readResource(uri string) (string, error) {
	var path string
	switch {
	case uri == "cortex://registry/context-mesh":
		path = c.cfg.RegistryPath
	case uri == "cortex://schemas/clyffy-touchpoint.v2":
		path = filepath.Join(c.cfg.RepoRoot, "schemas", "intelligence", "clyffy-touchpoint.v2.schema.json")
	case uri == "cortex://docs/naming":
		path = filepath.Join(c.cfg.RepoRoot, "docs", "WARDENCLYFFE_NAMING_CONVENTIONS.md")
	case uri == "cortex://docs/structure":
		path = filepath.Join(c.cfg.RepoRoot, "docs", "ai", "WARDENCLYFFE_STRUCTURE_STANDARD.md")
	case strings.HasPrefix(uri, "cortex://decisions/"):
		key := strings.TrimPrefix(uri, "cortex://decisions/")
		if key == "" || strings.ContainsAny(key, "/\\.") {
			return "", ErrUnknownResource
		}
		path = filepath.Join(c.cfg.DecisionsDir, key+".md")
	default:
		return "", ErrUnknownResource
	}
	b, err := os.ReadFile(path)
	if err != nil {
		return "", err
	}
	return string(b), nil
}

// decisionKeys lists project_keys from the decisions dir (filename = key).
func decisionKeys(dir string) ([]string, error) {
	entries, err := os.ReadDir(dir)
	if err != nil {
		return nil, err
	}
	keys := []string{}
	for _, e := range entries {
		if !e.IsDir() && strings.HasSuffix(e.Name(), ".md") {
			keys = append(keys, strings.TrimSuffix(e.Name(), ".md"))
		}
	}
	sort.Strings(keys)
	return keys, nil
}
