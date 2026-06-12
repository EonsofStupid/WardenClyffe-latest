// Clyffe storage domain service — the thin client onto the Go storage-broker
// control plane. Colocated with the domain (DDD). React never holds storage
// logic; it only calls Go.

import type { MountGrant, PurchaseInput, Volume } from "./types";

// Routed to storage-broker via the Warden edge / dev proxy.
const BASE = "/api/storage";

async function req<T>(path: string, init?: RequestInit): Promise<T> {
  const res = await fetch(path, {
    headers: { "content-type": "application/json" },
    ...init,
  });
  if (!res.ok) {
    let detail = res.statusText;
    try {
      const body = (await res.json()) as { error?: { message?: string } };
      if (body.error?.message) detail = body.error.message;
    } catch {
      /* non-JSON error body */
    }
    throw new Error(`storage ${init?.method ?? "GET"} ${path} failed: ${detail}`);
  }
  if (res.status === 204) return undefined as T;
  return (await res.json()) as T;
}

export const storageService = {
  list: (tenantId?: string) =>
    req<{ items: Volume[]; count: number }>(
      `${BASE}/volumes${tenantId ? `?tenant_id=${encodeURIComponent(tenantId)}` : ""}`,
    ),

  get: (id: string) => req<Volume>(`${BASE}/volumes/${encodeURIComponent(id)}`),

  purchase: (input: PurchaseInput) =>
    req<Volume>(`${BASE}/volumes`, { method: "POST", body: JSON.stringify(input) }),

  deprovision: (id: string) =>
    req<void>(`${BASE}/volumes/${encodeURIComponent(id)}`, { method: "DELETE" }),

  grantMount: (id: string, protocol: "s3" | "fuse" = "s3") =>
    req<MountGrant>(
      `${BASE}/volumes/${encodeURIComponent(id)}/mount-grant?protocol=${protocol}`,
      { method: "POST" },
    ),
};
