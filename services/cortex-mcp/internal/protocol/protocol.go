// Package protocol implements the MCP JSON-RPC wire over stdio: one JSON
// message per line, requests in on stdin, responses out on stdout. It is
// transport only — every capability is delegated to the catalog.
package protocol

import (
	"bufio"
	"encoding/json"
	"fmt"
	"io"
)

// Catalog is what the protocol serves: the registered tools, resources, and
// prompts. Implemented by catalog.Catalog.
type Catalog interface {
	ListTools() []ToolDef
	CallTool(name string, args json.RawMessage) (text string, isError bool)
	ListResources() []ResourceDef
	ReadResource(uri string) (text string, err error)
	ListPrompts() []PromptDef
	GetPrompt(name string, args map[string]string) (text string, err error)
}

// ToolDef is an MCP tool definition; InputSchema is JSON Schema.
type ToolDef struct {
	Name        string          `json:"name"`
	Description string          `json:"description"`
	InputSchema json.RawMessage `json:"inputSchema"`
}

// ResourceDef is an MCP resource definition.
type ResourceDef struct {
	URI         string `json:"uri"`
	Name        string `json:"name"`
	Description string `json:"description"`
	MimeType    string `json:"mimeType"`
}

// PromptDef is an MCP prompt definition.
type PromptDef struct {
	Name        string       `json:"name"`
	Description string       `json:"description"`
	Arguments   []PromptArg  `json:"arguments,omitempty"`
}

// PromptArg is a single prompt argument.
type PromptArg struct {
	Name        string `json:"name"`
	Description string `json:"description"`
	Required    bool   `json:"required"`
}

type request struct {
	JSONRPC string          `json:"jsonrpc"`
	ID      json.RawMessage `json:"id,omitempty"`
	Method  string          `json:"method"`
	Params  json.RawMessage `json:"params,omitempty"`
}

type response struct {
	JSONRPC string          `json:"jsonrpc"`
	ID      json.RawMessage `json:"id"`
	Result  any             `json:"result,omitempty"`
	Error   *rpcError       `json:"error,omitempty"`
}

type rpcError struct {
	Code    int    `json:"code"`
	Message string `json:"message"`
}

// Server serves the MCP protocol for one catalog.
type Server struct{ cat Catalog }

// NewServer returns a Server over the given catalog.
func NewServer(cat Catalog) *Server { return &Server{cat: cat} }

// Serve reads JSON-RPC lines from r and writes responses to w until EOF.
func (s *Server) Serve(r io.Reader, w io.Writer) error {
	in := bufio.NewScanner(r)
	in.Buffer(make([]byte, 0, 1<<20), 1<<24)
	out := json.NewEncoder(w)

	for in.Scan() {
		line := in.Bytes()
		if len(line) == 0 {
			continue
		}
		var req request
		if err := json.Unmarshal(line, &req); err != nil {
			continue // not a valid frame; MCP stdio ignores garbage lines
		}
		if req.ID == nil {
			continue // notification (e.g. notifications/initialized) — no reply
		}
		resp := s.handle(req)
		if err := out.Encode(resp); err != nil {
			return err
		}
	}
	return in.Err()
}

func (s *Server) handle(req request) response {
	ok := func(result any) response {
		return response{JSONRPC: "2.0", ID: req.ID, Result: result}
	}
	fail := func(code int, msg string) response {
		return response{JSONRPC: "2.0", ID: req.ID, Error: &rpcError{Code: code, Message: msg}}
	}

	switch req.Method {
	case "initialize":
		var p struct {
			ProtocolVersion string `json:"protocolVersion"`
		}
		_ = json.Unmarshal(req.Params, &p)
		if p.ProtocolVersion == "" {
			p.ProtocolVersion = "2025-06-18"
		}
		return ok(map[string]any{
			"protocolVersion": p.ProtocolVersion,
			"capabilities": map[string]any{
				"tools":     map[string]any{},
				"resources": map[string]any{},
				"prompts":   map[string]any{},
			},
			"serverInfo": map[string]any{"name": "cortex-mcp", "version": "0.1.0"},
		})

	case "ping":
		return ok(map[string]any{})

	case "tools/list":
		return ok(map[string]any{"tools": s.cat.ListTools()})

	case "tools/call":
		var p struct {
			Name      string          `json:"name"`
			Arguments json.RawMessage `json:"arguments"`
		}
		if err := json.Unmarshal(req.Params, &p); err != nil {
			return fail(-32602, "invalid params")
		}
		text, isErr := s.cat.CallTool(p.Name, p.Arguments)
		return ok(map[string]any{
			"content": []map[string]any{{"type": "text", "text": text}},
			"isError": isErr,
		})

	case "resources/list":
		return ok(map[string]any{"resources": s.cat.ListResources()})

	case "resources/read":
		var p struct {
			URI string `json:"uri"`
		}
		if err := json.Unmarshal(req.Params, &p); err != nil {
			return fail(-32602, "invalid params")
		}
		text, err := s.cat.ReadResource(p.URI)
		if err != nil {
			return fail(-32002, err.Error())
		}
		return ok(map[string]any{
			"contents": []map[string]any{{"uri": p.URI, "mimeType": "text/plain", "text": text}},
		})

	case "prompts/list":
		return ok(map[string]any{"prompts": s.cat.ListPrompts()})

	case "prompts/get":
		var p struct {
			Name      string            `json:"name"`
			Arguments map[string]string `json:"arguments"`
		}
		if err := json.Unmarshal(req.Params, &p); err != nil {
			return fail(-32602, "invalid params")
		}
		text, err := s.cat.GetPrompt(p.Name, p.Arguments)
		if err != nil {
			return fail(-32602, err.Error())
		}
		return ok(map[string]any{
			"messages": []map[string]any{
				{"role": "user", "content": map[string]any{"type": "text", "text": text}},
			},
		})

	default:
		return fail(-32601, fmt.Sprintf("method not found: %s", req.Method))
	}
}
