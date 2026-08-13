import { MOCK_WORKSPACES } from "./mock-data";
import type { CustomerWorkspace, OrderInput } from "./types";

/** In-memory mock store so the UI works without a live Warden API. */
let store: CustomerWorkspace[] = structuredClone(MOCK_WORKSPACES);

function delay(ms = 280) {
  return new Promise((r) => setTimeout(r, ms));
}

export const clyffeCodeService = {
  async list(): Promise<CustomerWorkspace[]> {
    await delay();
    return structuredClone(store);
  },

  async get(id: string): Promise<CustomerWorkspace | null> {
    await delay();
    return structuredClone(store.find((w) => w.id === id) ?? null);
  },

  async order(input: OrderInput): Promise<CustomerWorkspace> {
    await delay(500);
    const tierMeta: Record<string, { cores: number; memoryGiB: number; diskGiB: number }> = {
      starter: { cores: 4, memoryGiB: 8, diskGiB: 100 },
      builder: { cores: 8, memoryGiB: 16, diskGiB: 160 },
      "premium-pilot": { cores: 16, memoryGiB: 32, diskGiB: 320 },
      power: { cores: 28, memoryGiB: 64, diskGiB: 500 },
      gpu: { cores: 32, memoryGiB: 64, diskGiB: 500 },
    };
    const t = tierMeta[input.tier] ?? tierMeta.builder;
    const slug = input.name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-|-$/g, "")
      .slice(0, 32) || "workspace";
    const ws: CustomerWorkspace = {
      id: `ws_${Date.now().toString(36)}`,
      name: input.name || "New workspace",
      slug,
      tier: input.tier,
      state: "provisioning",
      region: input.region,
      repo: input.repo,
      connectTarget: "customer-devstation",
      agents: input.agents.length ? input.agents : ["claude", "cursor"],
      cores: t.cores,
      memoryGiB: t.memoryGiB,
      diskGiB: t.diskGiB,
    };
    store = [ws, ...store];
    // Simulate provision completing after order
    setTimeout(() => {
      store = store.map((w) =>
        w.id === ws.id ? { ...w, state: "running" as const } : w,
      );
    }, 4000);
    return structuredClone(ws);
  },

  async start(id: string): Promise<CustomerWorkspace | null> {
    await delay(400);
    store = store.map((w) =>
      w.id === id && w.state === "stopped" ? { ...w, state: "starting" as const } : w,
    );
    setTimeout(() => {
      store = store.map((w) =>
        w.id === id ? { ...w, state: "running" as const } : w,
      );
    }, 2000);
    return structuredClone(store.find((w) => w.id === id) ?? null);
  },

  async stop(id: string): Promise<CustomerWorkspace | null> {
    await delay(400);
    store = store.map((w) =>
      w.id === id && w.state === "running" ? { ...w, state: "stopped" as const } : w,
    );
    return structuredClone(store.find((w) => w.id === id) ?? null);
  },

  /** Mock open — marks last opened; real Connect does SSH behind scenes. */
  async open(id: string): Promise<{ workspace: CustomerWorkspace; sessionId: string }> {
    await delay(200);
    const now = new Date().toISOString();
    store = store.map((w) =>
      w.id === id
        ? {
            ...w,
            state: w.state === "stopped" ? ("starting" as const) : w.state,
            lastOpenedAt: now,
          }
        : w,
    );
    if (store.find((w) => w.id === id)?.state === "starting") {
      setTimeout(() => {
        store = store.map((w) =>
          w.id === id ? { ...w, state: "running" as const } : w,
        );
      }, 1500);
    }
    const workspace = store.find((w) => w.id === id);
    if (!workspace) throw new Error("Workspace not found");
    return {
      workspace: structuredClone(workspace),
      sessionId: `open_${Date.now().toString(36)}`,
    };
  },

  resetDemo() {
    store = structuredClone(MOCK_WORKSPACES);
  },
};
