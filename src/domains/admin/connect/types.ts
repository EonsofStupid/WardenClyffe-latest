// Admin · connect — types mirroring the Go contract (internal/connect).
export type ToolStatus = {
  tool: string;
  connected: boolean;
  detail: string;
};

export type ActivateResult = {
  ok: boolean;
  output?: string;
  error?: string;
};
