// Admin · edge service — thin client onto /api/warden/edge. Reads are open;
// mutations send the operator bearer (same storage key as useAuth).
import type { CreatePublicIP, PublicIP, UpdatePublicIP } from "./types";

const BASE = "/api/warden/edge";
const TOKEN_KEY = "warden.token";

function authHeaders(): HeadersInit {
  const token = localStorage.getItem(TOKEN_KEY) ?? "";
  return { authorization: `Bearer ${token}`, "content-type": "application/json" };
}

async function unwrap<T>(res: Response): Promise<T> {
  if (!res.ok) throw new Error((await res.json())?.error?.message ?? res.statusText);
  return (await res.json()) as T;
}

export const edgeService = {
  async listIPs(): Promise<{ items: PublicIP[]; count: number }> {
    return unwrap(await fetch(`${BASE}/ips`));
  },
  async createIP(input: CreatePublicIP): Promise<PublicIP> {
    return unwrap(await fetch(`${BASE}/ips`, { method: "POST", headers: authHeaders(), body: JSON.stringify(input) }));
  },
  async updateIP(id: string, input: UpdatePublicIP): Promise<PublicIP> {
    return unwrap(await fetch(`${BASE}/ips/${id}`, { method: "PATCH", headers: authHeaders(), body: JSON.stringify(input) }));
  },
};
