// Warden · mesh — types mirroring the Go contract (internal/mesh).
export type Plugin = {
  id: string;
  slug?: string;
  class?: string;
  status?: string;
  summary?: string;
};

export type ConnectDescriptors = {
  id: string;
  claude_desktop: unknown;
  codex_toml: string;
  claude_code: string;
};

export type IntelligenceInventory = {
  summary: { total: number; v2: number; v1_deprecated: number; sync_enabled: number; with_warnings: number };
  items: Record<string, unknown>[];
};
