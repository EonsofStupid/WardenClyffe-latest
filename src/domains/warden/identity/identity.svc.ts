// Warden · identity service — thin client onto /api/warden/identity.
import type { LoginResult, Operator } from "./types";

const BASE = "/api/warden/identity";

export const identityService = {
  async login(username: string, password: string): Promise<LoginResult> {
    const res = await fetch(`${BASE}/login`, {
      method: "POST",
      headers: { "content-type": "application/json" },
      body: JSON.stringify({ username, password }),
    });
    if (!res.ok) throw new Error("Invalid username or password");
    return (await res.json()) as LoginResult;
  },

  async me(token: string): Promise<Operator> {
    const res = await fetch(`${BASE}/me`, {
      headers: { authorization: `Bearer ${token}` },
    });
    if (!res.ok) throw new Error("unauthorized");
    return (await res.json()) as Operator;
  },

  async logout(token: string): Promise<void> {
    await fetch(`${BASE}/logout`, {
      method: "POST",
      headers: { authorization: `Bearer ${token}` },
    });
  },
};
