// Command cortex-mcp is the Cortex control-layer MCP server (stdio transport).
//
// Cortex exposes the WardenClyffe control + intelligence layers to MCP clients
// (Claude Code, Claude Desktop, Codex CLI): tools that capture decisions and
// inspect the mesh, resources that route to the authored truth, and prompts
// that carry the strict operator processes. Read+capture only — no
// infrastructure mutation. Spec: docs/specs/CORTEX_CONTROL_LAYER_SPEC.md.
package main

import (
	"log"
	"os"

	"github.com/wardenclyffe/cortex-mcp/internal/catalog"
	"github.com/wardenclyffe/cortex-mcp/internal/platform"
	"github.com/wardenclyffe/cortex-mcp/internal/protocol"
)

func main() {
	cfg, err := platform.LoadConfig()
	if err != nil {
		log.Fatalf("cortex-mcp: %v", err)
	}

	cat := catalog.New(cfg)
	srv := protocol.NewServer(cat)

	// stdio transport: JSON-RPC messages, one per line, stdin -> stdout.
	if err := srv.Serve(os.Stdin, os.Stdout); err != nil {
		log.Fatalf("cortex-mcp: %v", err)
	}
}
