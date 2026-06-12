// Warden · mesh service — thin client onto /api/warden/mesh.
import type { ConnectDescriptors, IntelligenceInventory, Plugin } from "./types";

const BASE = "/api/warden/mesh";

async function get<T>(path: string): Promise<T> {
  const res = await fetch(`${BASE}${path}`);
  if (!res.ok) throw new Error((await res.json())?.error?.message ?? res.statusText);
  return (await res.json()) as T;
}

export const meshService = {
  plugins: () => get<{ items: Plugin[]; count: number }>("/plugins"),
  connect: (id: string) => get<ConnectDescriptors>(`/plugins/${id}/connect`),
  intelligence: () => get<IntelligenceInventory>("/intelligence"),
};
