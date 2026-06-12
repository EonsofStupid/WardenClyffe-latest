package catalog

import (
	"bufio"
	"os"
	"strings"
)

// PluginEntry is one MCP server/gateway row from the Context Mesh registry.
type PluginEntry struct {
	ID      string `json:"id"`
	Slug    string `json:"slug,omitempty"`
	Class   string `json:"class,omitempty"`
	Status  string `json:"status,omitempty"`
	Summary string `json:"summary,omitempty"`
}

// parseRegistry extracts server/gateway entries from context-mesh.yaml with a
// line scan. Deliberately dependency-free: the registry shape is stable and
// owned by us; a YAML library lands with the Streamable HTTP phase if needed.
func parseRegistry(path string) ([]PluginEntry, error) {
	f, err := os.Open(path)
	if err != nil {
		return nil, err
	}
	defer f.Close()

	entries := []PluginEntry{}
	var cur *PluginEntry
	field := func(line, key string) (string, bool) {
		t := strings.TrimSpace(line)
		if v, ok := strings.CutPrefix(t, key+": "); ok {
			if i := strings.Index(v, "#"); i >= 0 {
				v = v[:i]
			}
			return strings.TrimSpace(v), true
		}
		return "", false
	}

	sc := bufio.NewScanner(f)
	sc.Buffer(make([]byte, 0, 1<<16), 1<<22)
	for sc.Scan() {
		line := sc.Text()
		if v, ok := field(line, "- id"); ok && strings.HasPrefix(v, "mcp.") {
			if cur != nil {
				entries = append(entries, *cur)
			}
			cur = &PluginEntry{ID: v}
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
		entries = append(entries, *cur)
	}
	return entries, sc.Err()
}
