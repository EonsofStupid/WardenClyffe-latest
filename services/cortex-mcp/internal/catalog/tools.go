package catalog

import (
	"encoding/json"
	"os/exec"
	"path/filepath"
	"strings"

	"github.com/wardenclyffe/cortex-mcp/internal/protocol"
)

func schema(s string) json.RawMessage { return json.RawMessage(s) }

func toolDefs() []protocol.ToolDef {
	return []protocol.ToolDef{
		{
			Name:        "cortex.commit_decision",
			Description: "Capture a locked WardenClyffe decision as a versioned v2 touchpoint (the durable domain memory). Wraps parking-lot-capture; the sync worker projects it to SurrealDB + Qdrant.",
			InputSchema: schema(`{
				"type": "object",
				"properties": {
					"type": {"type": "string", "enum": ["dependency", "feature", "capability"]},
					"name": {"type": "string", "description": "short decision name"},
					"decision": {"type": "string", "description": "what was locked in and why"},
					"boundary": {"type": "string", "description": "repo-relative path the decision concerns"},
					"focus": {"type": "string", "default": "Building WardenClyffe"}
				},
				"required": ["type", "name", "decision", "boundary"],
				"additionalProperties": false
			}`),
		},
		{
			Name:        "cortex.validate_touchpoints",
			Description: "Run the touchpoint validator and return the JSON inventory (v1/v2 counts, sync flags, warnings).",
			InputSchema: schema(`{"type": "object", "properties": {}, "additionalProperties": false}`),
		},
		{
			Name:        "cortex.search_decisions",
			Description: "Search the parking-lot decision touchpoints for a query string; returns matching decisions with their file paths.",
			InputSchema: schema(`{
				"type": "object",
				"properties": {"query": {"type": "string", "minLength": 2}},
				"required": ["query"],
				"additionalProperties": false
			}`),
		},
		{
			Name:        "cortex.list_plugins",
			Description: "List the MCP servers and gateways registered in the Context Mesh registry (id, class, status, summary).",
			InputSchema: schema(`{"type": "object", "properties": {}, "additionalProperties": false}`),
		},
	}
}

func (c *Catalog) commitDecision(args json.RawMessage) (string, bool) {
	var in struct {
		Type     string `json:"type"`
		Name     string `json:"name"`
		Decision string `json:"decision"`
		Boundary string `json:"boundary"`
		Focus    string `json:"focus"`
	}
	if err := json.Unmarshal(args, &in); err != nil {
		return "invalid arguments: " + err.Error(), true
	}
	if in.Type == "" || in.Name == "" || in.Decision == "" || in.Boundary == "" {
		return "type, name, decision, and boundary are required", true
	}
	if in.Focus == "" {
		in.Focus = "Building WardenClyffe"
	}
	out, err := c.runScript("parking-lot-capture.py",
		"--type", in.Type, "--name", in.Name,
		"--decision", in.Decision, "--boundary", in.Boundary, "--focus", in.Focus)
	if err != nil {
		return out + "\n" + err.Error(), true
	}
	return out, false
}

func (c *Catalog) validateTouchpoints() (string, bool) {
	out, err := c.runScript("validate-touchpoints.py", "--json")
	if err != nil {
		return out + "\n" + err.Error(), true
	}
	return out, false
}

func (c *Catalog) searchDecisions(args json.RawMessage) (string, bool) {
	var in struct {
		Query string `json:"query"`
	}
	if err := json.Unmarshal(args, &in); err != nil || strings.TrimSpace(in.Query) == "" {
		return "query is required", true
	}
	matches, err := searchFiles(c.cfg.DecisionsDir, in.Query)
	if err != nil {
		return err.Error(), true
	}
	if len(matches) == 0 {
		return "no decisions match: " + in.Query, false
	}
	b, _ := json.MarshalIndent(matches, "", "  ")
	return string(b), false
}

func (c *Catalog) listPlugins() (string, bool) {
	entries, err := parseRegistry(c.cfg.RegistryPath)
	if err != nil {
		return err.Error(), true
	}
	b, _ := json.MarshalIndent(entries, "", "  ")
	return string(b), false
}

// runScript invokes a foundation script from the repo root.
func (c *Catalog) runScript(script string, args ...string) (string, error) {
	cmd := exec.Command(c.cfg.PythonBin,
		append([]string{filepath.Join(c.cfg.RepoRoot, "scripts", "foundation", script)}, args...)...)
	cmd.Dir = c.cfg.RepoRoot
	out, err := cmd.CombinedOutput()
	return strings.TrimSpace(string(out)), err
}
