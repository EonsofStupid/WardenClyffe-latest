// Warden · mesh service — thin client onto /api/warden/mesh.
import type {
  ConnectDescriptors,
  IntelligenceInventory,
  Plugin,
  ProjectionResult,
  SyncRunResult,
} from "./types";

const BASE = "/api/warden/mesh";
const TOKEN_KEY = "warden.token"; // mirrors useAuth's storage key

async function get<T>(path: string): Promise<T> {
  const res = await fetch(`${BASE}${path}`);
  if (!res.ok) throw new Error((await res.json())?.error?.message ?? res.statusText);
  return (await res.json()) as T;
}

async function post<T>(path: string): Promise<T> {
  const token = localStorage.getItem(TOKEN_KEY) ?? "";
  const res = await fetch(`${BASE}${path}`, {
    method: "POST",
    headers: { authorization: `Bearer ${token}` },
  });
  if (!res.ok) throw new Error((await res.json())?.error?.message ?? res.statusText);
  return (await res.json()) as T;
}

export const meshService = {
  plugins: () => get<{ items: Plugin[]; count: number }>("/plugins"),
  connect: (id: string) => get<ConnectDescriptors>(`/plugins/${id}/connect`),
  intelligence: () => get<IntelligenceInventory>("/intelligence"),
  projection: () => get<ProjectionResult>("/projection"),
  runSync: () => post<SyncRunResult>("/sync/run"),
};
