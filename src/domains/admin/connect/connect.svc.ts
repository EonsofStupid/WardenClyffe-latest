// Admin · connect service — thin client onto /api/warden/connect. Status is
// open; activations send the operator bearer and a secret in the body (the
// secret never appears in a URL or log).
import type { ActivateResult, ToolStatus } from "./types";

const BASE = "/api/warden/connect";
const TOKEN_KEY = "warden.token";

function authHeaders(): HeadersInit {
  const token = localStorage.getItem(TOKEN_KEY) ?? "";
  return { authorization: `Bearer ${token}`, "content-type": "application/json" };
}

async function unwrap<T>(res: Response): Promise<T> {
  if (!res.ok) throw new Error((await res.json())?.error?.message ?? res.statusText);
  return (await res.json()) as T;
}

export const connectService = {
  async status(): Promise<{ items: ToolStatus[]; count: number }> {
    return unwrap(await fetch(`${BASE}/status`));
  },
  async activateInfisical(clientSecret: string): Promise<ActivateResult> {
    const res = await fetch(`${BASE}/infisical`, {
      method: "POST",
      headers: authHeaders(),
      body: JSON.stringify({ client_secret: clientSecret }),
    });
    return (await res.json()) as ActivateResult;
  },
  async activateGitHub(token: string): Promise<ActivateResult> {
    const res = await fetch(`${BASE}/github`, {
      method: "POST",
      headers: authHeaders(),
      body: JSON.stringify({ token }),
    });
    return (await res.json()) as ActivateResult;
  },
};
