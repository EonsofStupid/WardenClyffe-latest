package catalog

import (
	"errors"
	"strings"

	"github.com/wardenclyffe/cortex-mcp/internal/protocol"
)

// ErrUnknownPrompt means the prompt name does not resolve.
var ErrUnknownPrompt = errors.New("unknown prompt")

// promptDefs lists the strict operator processes. These are the portable,
// cross-tool form (Codex and Claude Desktop have no skills/hooks); the Claude
// Code plugin wraps them as thin skills.
func promptDefs() []protocol.PromptDef {
	return []protocol.PromptDef{
		{
			Name:        "planning-workflow",
			Description: "Strict WardenClyffe planning process: every locked dependency/feature/capability is captured as a versioned decision touchpoint (the durable domain memory).",
			Arguments: []protocol.PromptArg{
				{Name: "topic", Description: "what is being planned", Required: true},
			},
		},
		{
			Name:        "boundary-guard-checklist",
			Description: "Pre-creation review before any new folder/module/service/domain boundary.",
			Arguments: []protocol.PromptArg{
				{Name: "proposed_path", Description: "repo-relative path to be created", Required: true},
			},
		},
		{
			Name:        "build-spec",
			Description: "Author a build spec the WardenClyffe way: acceptance criteria first, components with named files/functions per the naming law, PR build order, non-goals.",
			Arguments: []protocol.PromptArg{
				{Name: "subject", Description: "what the spec builds", Required: true},
			},
		},
	}
}

func renderPrompt(name string, args map[string]string) (string, error) {
	get := func(k string) string { return strings.TrimSpace(args[k]) }
	switch name {
	case "planning-workflow":
		return `You are in WardenClyffe FORCED planning mode for: ` + get("topic") + `

Follow these steps exactly; do not skip or reorder:
1. Read cortex://docs/naming and cortex://docs/structure — all names must obey them.
2. State the active focus feature (default: Building WardenClyffe).
3. Identify the boundary (repo-relative path) each decision concerns; if the
   boundary would be NEW, run the boundary-guard-checklist prompt first.
4. For EVERY locked dependency, feature, or capability, immediately call the
   tool cortex.commit_decision with {type, name, decision, boundary}. The
   decision text states WHAT was locked and WHY. Never hand-write to any
   database — the touchpoint is the capture; the sync worker projects it.
5. Operator approval is required for: any new name (var/function/file/folder),
   any new boundary, reversing a prior lock.
6. Finish by calling cortex.validate_touchpoints and reporting warnings.`, nil

	case "boundary-guard-checklist":
		return `Boundary-guard pre-creation review for: ` + get("proposed_path") + `

1. Call cortex.search_decisions with the path's key words — has this boundary
   (or a synonym) already been decided or rejected?
2. Read cortex://docs/structure — does the path fit the canonical shape
   (services/<kebab>, internal/<context>, src/domains/<domain>/<context>)?
3. Read cortex://docs/naming — kebab-case dirs, no synonyms of existing
   boundaries (forbidden drift), package == context.
4. If it duplicates or overlaps an existing boundary: STOP and extend the
   existing one instead.
5. If genuinely new: present the name to the operator for approval BEFORE
   creating anything, then capture the approval via cortex.commit_decision.`, nil

	case "build-spec":
		return `Author a WardenClyffe build spec for: ` + get("subject") + `

Structure (see docs/specs/CORTEX_CONTROL_LAYER_SPEC.md as the model):
1. v2 clyffy_touchpoint frontmatter; UPPER_SNAKE filename under docs/specs/.
2. Acceptance criteria FIRST, written from the operator's seat (verifiable).
3. Components: exact paths + named files per the structure standard; functions
   and endpoints named per the naming law (<schema>.<verb>_<object>,
   Verb/VerbObject methods, <domain>.<verb>_<object> tools).
4. Build order: one PR per component, each with its own verify step.
5. Non-goals: state what this spec deliberately excludes.
6. Capture the spec itself via cortex.commit_decision, then run
   cortex.validate_touchpoints and resolve any warnings.`, nil

	default:
		return "", ErrUnknownPrompt
	}
}
