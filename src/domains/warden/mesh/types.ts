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

// ProjectionPlan mirrors the intelligence-sync plan written to W. Summary keys
// are the Go struct field names (capitalized) and any may be omitted at zero.
export type ProjectionPlan = {
  summary: { Total?: number; New?: number; Changed?: number; Unchanged?: number };
  projections: {
    project_key: string;
    workspace_id: string;
    path: string;
    kind: string;
    status: "new" | "changed" | "unchanged";
    to_surreal: boolean;
    to_qdrant: boolean;
  }[];
};

export type ProjectionResult = { present: boolean; plan: ProjectionPlan | null };
export type SyncRunResult = { ran: boolean; plan: ProjectionPlan | null };
