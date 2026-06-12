// Package catalog registers what Cortex serves: the control-layer tools, the
// intelligence-layer resources, and the strict-process prompts. Read+capture
// only — no tool here mutates infrastructure.
package catalog

import (
	"encoding/json"

	"github.com/wardenclyffe/cortex-mcp/internal/platform"
	"github.com/wardenclyffe/cortex-mcp/internal/protocol"
)

// Catalog implements protocol.Catalog over the WardenClyffe repo.
type Catalog struct{ cfg platform.Config }

// New returns the catalog for one repo root.
func New(cfg platform.Config) *Catalog { return &Catalog{cfg: cfg} }

// ListTools returns the cortex.* tool definitions.
func (c *Catalog) ListTools() []protocol.ToolDef { return toolDefs() }

// CallTool dispatches one tool call by name.
func (c *Catalog) CallTool(name string, args json.RawMessage) (string, bool) {
	switch name {
	case "cortex.commit_decision":
		return c.commitDecision(args)
	case "cortex.validate_touchpoints":
		return c.validateTouchpoints()
	case "cortex.search_decisions":
		return c.searchDecisions(args)
	case "cortex.list_plugins":
		return c.listPlugins()
	default:
		return "unknown tool: " + name, true
	}
}

// ListResources returns the cortex:// resource definitions.
func (c *Catalog) ListResources() []protocol.ResourceDef { return c.resourceDefs() }

// ReadResource resolves one cortex:// URI to file content.
func (c *Catalog) ReadResource(uri string) (string, error) { return c.readResource(uri) }

// ListPrompts returns the strict-process prompt definitions.
func (c *Catalog) ListPrompts() []protocol.PromptDef { return promptDefs() }

// GetPrompt renders one strict process with its arguments.
func (c *Catalog) GetPrompt(name string, args map[string]string) (string, error) {
	return renderPrompt(name, args)
}
