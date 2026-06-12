// Package mesh owns the Context Mesh registry view: which MCP plugins exist,
// how to connect each tool to them, and the touchpoint-inventory health.
// Read-only; the registry file is the source of truth (never duplicated).
package mesh

import (
	"bufio"
	"context"
	"encoding/json"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

// Plugin is one MCP server/gateway row from context-mesh.yaml.
type Plugin struct {
	ID      string `json:"id"`
	Slug    string `json:"slug,omitempty"`
	Class   string `json:"class,omitempty"`
	Status  string `json:"status,omitempty"`
	Summary string `json:"summary,omitempty"`
}

// ConnectDescriptors are the copyable per-tool snippets for one plugin.
type ConnectDescriptors struct {
	ID            string `json:"id"`
	ClaudeDesktop any    `json:"claude_desktop"`
	CodexTOML     string `json:"codex_toml"`
	ClaudeCode    string `json:"claude_code"`
}

// Store reads the registry and the validator inventory from the repo.
type Store struct{ repoRoot string }

// NewStore returns a mesh store rooted at the repo.
func NewStore(repoRoot string) *Store { return &Store{repoRoot: repoRoot} }

// ListPlugins parses servers + gateways out of context-mesh.yaml (line scan;
// registry shape is ours and stable).
func (s *Store) ListPlugins() ([]Plugin, error) {
	f, err := os.Open(filepath.Join(s.repoRoot, "wardenclyffe", "registry", "context-mesh.yaml"))
	if err != nil {
		return nil, err
	}
	defer f.Close()
	field := func(line, key string) (string, bool) {
		if v, ok := strings.CutPrefix(strings.TrimSpace(line), key+": "); ok {
			if i := strings.Index(v, "#"); i >= 0 {
				v = v[:i]
			}
			return strings.TrimSpace(v), true
		}
		return "", false
	}
	out := []Plugin{}
	var cur *Plugin
	sc := bufio.NewScanner(f)
	sc.Buffer(make([]byte, 0, 1<<16), 1<<22)
	for sc.Scan() {
		line := sc.Text()
		if v, ok := field(line, "- id"); ok && strings.HasPrefix(v, "mcp.") {
			if cur != nil {
				out = append(out, *cur)
			}
			cur = &Plugin{ID: v}
			continue
		}
		if cur == nil {
			continue
		}
		if v, ok := field(line, "slug"); ok && cur.Slug == "" {
			cur.Slug = v
		} else if v, ok := field(line, "class"); ok && cur.Class == "" {
			cur.Class = v
		} else if v, ok := field(line, "status"); ok && cur.Status == "" {
			cur.Status = v
		} else if v, ok := field(line, "summary"); ok && cur.Summary == "" {
			cur.Summary = v
		}
	}
	if cur != nil {
		out = append(out, *cur)
	}
	return out, sc.Err()
}

// wPlugins maps a registry id to its W-drive plugin entry args, if shipped.
var wPlugins = map[string][]string{
	"mcp.workspace.clyffy-master.cortex":       {"control"},
	"mcp.workspace.clyffy-master.intelligence": {"intelligence"},
}

// GetConnectDescriptors renders the per-tool connect snippets (env-refs only,
// never secret values) for plugins shipped on W.
func (s *Store) GetConnectDescriptors(id string) (*ConnectDescriptors, bool) {
	args, ok := wPlugins[id]
	if !ok {
		return nil, false
	}
	bin := "/workspace/warden-storage/plugins/bin/cortex-mcp"
	layer := args[0]
	return &ConnectDescriptors{
		ID: id,
		ClaudeDesktop: map[string]any{
			"mcpServers": map[string]any{
				"cortex-" + layer: map[string]any{
					"command": "ssh",
					"args":    []string{"warden-devstation", bin, layer},
				},
			},
		},
		CodexTOML: "[mcp_servers.cortex_" + layer + "]\ncommand = \"ssh\"\nargs = [\"warden-devstation\", \"" + bin + "\", \"" + layer + "\"]",
		ClaudeCode: "claude mcp add cortex-" + layer + " -- " + bin + " " + layer,
	}, true
}

// IntelligenceInventory runs the touchpoint validator and returns its JSON
// inventory plus summary counts.
func (s *Store) IntelligenceInventory(ctx context.Context) (map[string]any, error) {
	cmd := exec.CommandContext(ctx, "python3",
		filepath.Join(s.repoRoot, "scripts", "foundation", "validate-touchpoints.py"), "--json")
	cmd.Dir = s.repoRoot
	raw, err := cmd.Output()
	if err != nil {
		return nil, err
	}
	var items []map[string]any
	if err := json.Unmarshal(raw, &items); err != nil {
		return nil, err
	}
	v1, v2, sync, warn := 0, 0, 0, 0
	for _, it := range items {
		if v, _ := it["version"].(float64); v >= 2 {
			v2++
		} else {
			v1++
		}
		if b, _ := it["sync_surreal"].(bool); b {
			sync++
		}
		if w, _ := it["warnings"].([]any); len(w) > 0 {
			warn++
		}
	}
	return map[string]any{
		"summary": map[string]int{"total": len(items), "v2": v2, "v1_deprecated": v1, "sync_enabled": sync, "with_warnings": warn},
		"items":   items,
	}, nil
}
