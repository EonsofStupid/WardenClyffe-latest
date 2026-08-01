/** Clyffe Code customer-facing workspace model (mock-first for redline). */

export type WorkspaceTier = "starter" | "builder" | "premium-pilot" | "power" | "gpu";

export type WorkspaceState =
  | "running"
  | "starting"
  | "stopped"
  | "provisioning"
  | "error";

export type AgentReady = "claude" | "codex" | "grok" | "cursor-remote";

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
