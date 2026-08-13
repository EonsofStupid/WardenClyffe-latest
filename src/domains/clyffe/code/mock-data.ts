import type { CustomerWorkspace, OpenStep } from "./types";

/** Seed workspaces for redline — not live fleet data. */
export const MOCK_WORKSPACES: CustomerWorkspace[] = [
  {
    id: "ws_demo_builder",
    name: "My vibe box",
    slug: "my-vibe-box",
    tier: "builder",
    state: "running",
    region: "us-east",
    repo: "github.com/you/side-project",
    connectTarget: "customer-devstation",
    agents: ["claude", "codex", "grok", "gemini", "cursor"],
    cores: 8,
    memoryGiB: 16,
    diskGiB: 160,
    lastOpenedAt: "2026-07-30T18:22:00Z",
    previewHint: "localhost:5173 via Connect",
  },
  {
    id: "ws_demo_starter",
    name: "Landing site",
    slug: "landing-site",
    tier: "starter",
    state: "stopped",
    region: "us-east",
    repo: "github.com/you/landing",
    connectTarget: "customer-devstation",
    agents: ["claude", "cursor"],
    cores: 4,
    memoryGiB: 8,
    diskGiB: 100,
    lastOpenedAt: "2026-07-28T11:05:00Z",
  },
  {
    id: "ws_demo_provision",
    name: "Agent lab",
    slug: "agent-lab",
    tier: "power",
    state: "provisioning",
    region: "us-east",
    connectTarget: "customer-devstation",
    agents: ["claude", "codex", "grok"],
    cores: 24,
    memoryGiB: 64,
    diskGiB: 500,
  },
];

/** Double-click open choreography — customer vs behind-the-scenes. */
export const OPEN_STEPS: OpenStep[] = [
  {
    id: "auth",
    label: "Sign in",
    detail: "Session already on this device",
    customerSees: "Clyffe knows who you are",
    behindScenes: "Clyffe session / device enrollment token",
  },
  {
    id: "resolve",
    label: "Find workspace",
    detail: "Which cloud box is yours",
    customerSees: "Opening “My vibe box”…",
    behindScenes: "Warden inventory: tenant workspace id + tier + state",
  },
  {
    id: "path",
    label: "Private path",
    detail: "No public SSH homework",
    customerSees: "Connecting securely…",
    behindScenes: "Jump / WardenNet / split DNS → customer-devstation",
  },
  {
    id: "tunnel",
    label: "Local bridge",
    detail: "Desktop feels local",
    customerSees: "Almost there…",
    behindScenes: "SSH / Remote-SSH / tunnel ports for shell + previews",
  },
  {
    id: "shell",
    label: "Workspace ready",
    detail: "Remote home + repo",
    customerSees: "Your coding cloud is online",
    behindScenes: "Shell in workspace user; preflight env if needed",
  },
  {
    id: "agents",
    label: "AI tools",
    detail: "Claude · Codex · Grok on the box",
    customerSees: "Agents ready on the remote machine",
    behindScenes: "CLIs preinstalled on clean template; keys via secrets broker",
  },
  {
    id: "ready",
    label: "You’re in",
    detail: "Work from your machine",
    customerSees: "Terminal / editor attached — you never typed SSH",
    behindScenes: "Audit open event; task complete",
  },
];

export const TIER_OPTIONS = [
  { id: "starter" as const, label: "Starter", blurb: "2–4 vCPU · 8 GiB — light sites & docs", cores: 4, memoryGiB: 8, diskGiB: 100 },
  { id: "builder" as const, label: "Builder", blurb: "8 vCPU · 16 GiB — full-stack + agents", cores: 8, memoryGiB: 16, diskGiB: 160 },
  { id: "premium-pilot" as const, label: "Premium Pilot", blurb: "16 vCPU · 32 GiB — flagship agent work", cores: 16, memoryGiB: 32, diskGiB: 320 },
  { id: "power" as const, label: "Power", blurb: "24–32 vCPU · 64 GiB — heavy monorepos", cores: 28, memoryGiB: 64, diskGiB: 500 },
];
