/**
 * The client for `automaton serve`.
 *
 * Everything else in this domain is mock-first (`code.svc.ts`) because the
 * Warden API for provisioning does not exist yet. This one is not a mock: the
 * engine is real, and the events it returns are the same NDJSON the terminal
 * pane renders. See `automaton/docs/20-http-surface.md`.
 *
 * Connection details are read from Vite env for now. The real answer is that the
 * control plane hands out a per-workspace engine endpoint and a scoped token
 * when a devspace is opened — that is WP-8, and until then this is explicit
 * rather than pretending to be provisioned.
 */

import {
  CapabilityRefused,
  type AutomatonEvent,
  type CapabilityGap,
  type CapabilityRow,
  type CapsResponse,
  type EngineSession,
  type ProviderReadiness,
  type TurnSpec,
} from "./engine.types";

const STORAGE_KEY = "clyffe.code.engine";

export type EngineTarget = { url: string; token: string };

/**
 * Env is the default; localStorage lets an operator point the devspace at a
 * different box without a rebuild. Neither is a substitute for the control
 * plane issuing a scoped token.
 */
export function engineTarget(): EngineTarget | null {
  if (typeof window !== "undefined") {
    try {
      const raw = window.localStorage.getItem(STORAGE_KEY);
      if (raw) {
        const parsed = JSON.parse(raw) as Partial<EngineTarget>;
        if (parsed.url && parsed.token) {
          return { url: parsed.url.replace(/\/$/, ""), token: parsed.token };
        }
      }
    } catch {
      /* a corrupt entry must not take the page down */
    }
  }
  const url = import.meta.env.VITE_AUTOMATON_URL as string | undefined;
  const token = import.meta.env.VITE_AUTOMATON_TOKEN as string | undefined;
  if (!url || !token) return null;
  return { url: url.replace(/\/$/, ""), token };
}

export function setEngineTarget(target: EngineTarget | null) {
  if (typeof window === "undefined") return;
  if (!target) window.localStorage.removeItem(STORAGE_KEY);
  else window.localStorage.setItem(STORAGE_KEY, JSON.stringify(target));
}

function requireTarget(): EngineTarget {
  const t = engineTarget();
  if (!t) {
    throw new Error(
      "No engine configured. Run `automaton serve` in the workspace and set VITE_AUTOMATON_URL / VITE_AUTOMATON_TOKEN."
    );
  }
  return t;
}

async function call<T>(path: string, init: RequestInit = {}): Promise<T> {
  const { url, token } = requireTarget();
  const res = await fetch(`${url}${path}`, {
    ...init,
    headers: {
      authorization: `Bearer ${token}`,
      ...(init.body ? { "content-type": "application/json" } : {}),
      ...(init.headers ?? {}),
    },
  });
  if (!res.ok) {
    throw new Error(`${init.method ?? "GET"} ${path} → ${res.status}`);
  }
  return (await res.json()) as T;
}

export type TurnHandlers = {
  onSession?: (sessionId: string) => void;
  /** Non-blocking gaps: the turn runs, with less than was asked for. */
  onDegraded?: (gap: CapabilityGap) => void;
  onEvent?: (event: AutomatonEvent) => void;
  onDone?: (result: { sessionId: string; status: string }) => void;
};

export const engineService = {
  configured(): boolean {
    return engineTarget() !== null;
  },

  async health(): Promise<boolean> {
    const t = engineTarget();
    if (!t) return false;
    try {
      const res = await fetch(`${t.url}/api/health`);
      return res.ok;
    } catch {
      return false;
    }
  },

  caps(): Promise<CapsResponse> {
    return call<CapsResponse>("/api/caps");
  },

  seatCaps(provider: string): Promise<{ provider: string; capabilities: CapabilityRow[] }> {
    return call(`/api/caps?provider=${encodeURIComponent(provider)}`);
  },

  providers(): Promise<ProviderReadiness[]> {
    return call<ProviderReadiness[]>("/api/providers");
  },

  sessions(): Promise<EngineSession[]> {
    return call<EngineSession[]>("/api/sessions");
  },

  events(sessionId: string): Promise<AutomatonEvent[]> {
    return call<AutomatonEvent[]>(`/api/sessions/${encodeURIComponent(sessionId)}/events`);
  },

  /**
   * Start a turn and consume its stream.
   *
   * `fetch` rather than `EventSource`, because EventSource cannot POST and
   * cannot send an Authorization header — putting the token in the query string
   * would leak it into every proxy log between here and the box.
   */
  async startTurn(
    input: { provider: string; prompt: string; spec?: TurnSpec },
    handlers: TurnHandlers = {},
    signal?: AbortSignal
  ): Promise<void> {
    const { url, token } = requireTarget();
    const res = await fetch(`${url}/api/sessions`, {
      method: "POST",
      signal,
      headers: { authorization: `Bearer ${token}`, "content-type": "application/json" },
      body: JSON.stringify(input),
    });

    if (res.status === 422) {
      const body = (await res.json()) as {
        blocking: CapabilityGap[];
        degraded: CapabilityGap[];
      };
      throw new CapabilityRefused(body.blocking ?? [], body.degraded ?? []);
    }
    if (!res.ok || !res.body) {
      throw new Error(`POST /api/sessions → ${res.status}`);
    }

    await consumeSse(res.body, (event, data) => {
      if (event === "session") handlers.onSession?.((data as { sessionId: string }).sessionId);
      else if (event === "degraded") handlers.onDegraded?.(data as CapabilityGap);
      else if (event === "event") handlers.onEvent?.(data as AutomatonEvent);
      else if (event === "done")
        handlers.onDone?.(data as { sessionId: string; status: string });
    });
  },

  /**
   * Follow a session someone else started — another pane, another host. The
   * stream replays the journal first, so a late reader still sees the whole
   * conversation.
   */
  async follow(
    sessionId: string,
    handlers: { onEvent?: (e: AutomatonEvent) => void; onReplayed?: (count: number) => void },
    signal?: AbortSignal
  ): Promise<void> {
    const { url, token } = requireTarget();
    const res = await fetch(
      `${url}/api/sessions/${encodeURIComponent(sessionId)}/events?stream=1`,
      { headers: { authorization: `Bearer ${token}` }, signal }
    );
    if (!res.ok || !res.body) throw new Error(`follow ${sessionId} → ${res.status}`);

    await consumeSse(res.body, (event, data) => {
      if (event === "event") handlers.onEvent?.(data as AutomatonEvent);
      else if (event === "replayed")
        handlers.onReplayed?.((data as { count: number }).count);
    });
  },
};

/**
 * Minimal SSE reader. A chunk can split a frame anywhere, so the remainder is
 * held until its blank-line terminator arrives — otherwise a long assistant
 * message arrives as broken JSON.
 */
async function consumeSse(
  body: ReadableStream<Uint8Array>,
  onFrame: (event: string, data: unknown) => void
): Promise<void> {
  const reader = body.getReader();
  const decoder = new TextDecoder();
  let buffer = "";

  try {
    for (;;) {
      const { value, done } = await reader.read();
      if (done) break;
      buffer += decoder.decode(value, { stream: true });

      let split = buffer.indexOf("\n\n");
      while (split !== -1) {
        const frame = buffer.slice(0, split);
        buffer = buffer.slice(split + 2);
        split = buffer.indexOf("\n\n");

        const trimmed = frame.trim();
        if (!trimmed || trimmed.startsWith(":")) continue; // keep-alive
        const event = /^event: (.+)$/m.exec(trimmed)?.[1] ?? "message";
        const raw = /^data: (.+)$/m.exec(trimmed)?.[1];
        if (!raw) continue;
        try {
          onFrame(event, JSON.parse(raw));
        } catch {
          /* a torn frame is skipped, never thrown at the view */
        }
      }
    }
  } finally {
    reader.releaseLock();
  }
}
