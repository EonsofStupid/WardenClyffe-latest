import type { GuestActionResult, ProxmoxGuest, ProxmoxStatus } from "./types";

async function req<T>(path: string, init?: RequestInit): Promise<T> {
  const res = await fetch(path, {
    headers: { "Content-Type": "application/json" },
    ...init,
  });
  const text = await res.text();
  const body = text ? JSON.parse(text) : null;
  if (!res.ok) {
    const msg = body?.error?.message ?? body?.error ?? res.statusText;
    throw new Error(typeof msg === "string" ? msg : res.statusText);
  }
  return body as T;
}

export const proxmoxService = {
  status: () => req<ProxmoxStatus>("/api/warden/proxmox/status"),
  guests: () => req<{ count: number; items: ProxmoxGuest[] }>("/api/warden/proxmox/guests"),
  start: (node: string, kind: string, vmid: number) =>
    req<GuestActionResult>(`/api/warden/proxmox/guests/${node}/${kind}/${vmid}/start`, {
      method: "POST",
    }),
  stop: (node: string, kind: string, vmid: number) =>
    req<GuestActionResult>(`/api/warden/proxmox/guests/${node}/${kind}/${vmid}/stop`, {
      method: "POST",
    }),
};
