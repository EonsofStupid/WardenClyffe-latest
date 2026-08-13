/** Clyffe Code customer-facing workspace model (mock-first for redline). */

export type WorkspaceTier = "starter" | "builder" | "premium-pilot" | "power" | "gpu";

export type WorkspaceState =
  | "running"
  | "starting"
  | "stopped"
  | "provisioning"
  | "error";

/**
 * Seat ids, and they are the **engine's** ids — not a parallel vocabulary.
 *
 * Automaton's kernel drives these five providers; anything this list says that
 * the kernel does not have is a seat the product promises and cannot deliver.
 * `gemini` is the id because that is what the adapter is called; the seat itself
 * runs Antigravity (`agy`), which is what the customer sees — see
 * `AGENT_LABELS`.
 */
export type AgentReady = "claude" | "codex" | "grok" | "gemini" | "cursor";

/** What each seat is called to a customer, as opposed to in the engine. */
export const AGENT_LABELS: Record<AgentReady, string> = {
  claude: "Claude Code",
  codex: "Codex",
  grok: "Grok",
  gemini: "Antigravity",
  cursor: "Cursor",
};

export const ALL_AGENTS: AgentReady[] = ["claude", "codex", "grok", "gemini", "cursor"];

export type CustomerWorkspace = {
  id: string;
  name: string;
  slug: string;
  tier: WorkspaceTier;
  state: WorkspaceState;
  region: string;
  repo?: string;
  /** What the user never types — Connect resolves this. */
  connectTarget: string;
  agents: AgentReady[];
  cores: number;
  memoryGiB: number;
  diskGiB: number;
  lastOpenedAt?: string;
  previewHint?: string;
};

export type OpenStepId =
  | "auth"
  | "resolve"
  | "path"
  | "tunnel"
  | "shell"
  | "agents"
  | "ready";

export type OpenStep = {
  id: OpenStepId;
  label: string;
  detail: string;
  /** Customer-facing vs sysadmin truth */
  customerSees: string;
  behindScenes: string;
};

export type OrderInput = {
  name: string;
  tier: WorkspaceTier;
  repo?: string;
  region: string;
  agents: AgentReady[];
};
