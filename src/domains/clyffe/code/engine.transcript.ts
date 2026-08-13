/**
 * Events → a transcript the view can render.
 *
 * The terminal renderer (`automaton/engine/ui/render.mjs`) turns the same events
 * into `{text, fg}` spans with line directives — open/append/close — because a
 * terminal writer only concatenates and newlines. React does not need that: it
 * re-renders a row when the row changes. So this reuses the *vocabulary* (seat
 * tokens, event types) and not the line machinery, which is a terminal concern.
 *
 * Kept pure and separate from the view so it can be reasoned about on its own:
 * given a list of events, the transcript is always the same.
 */

import type { AutomatonEvent } from "./engine.types";

export type TranscriptRow =
  | { kind: "user"; id: string; ts: string; text: string }
  | {
      kind: "assistant";
      id: string;
      ts: string;
      provider: string;
      text: string;
      streaming: boolean;
    }
  | { kind: "tool"; id: string; ts: string; name: string; state: "started" | "finished"; detail?: string }
  | { kind: "notice"; id: string; ts: string; tone: "info" | "warn" | "danger"; text: string }
  | { kind: "artifact"; id: string; ts: string; text: string };

/** Provider → the token name the palette uses for that seat. */
export const SEAT_TOKEN: Record<string, string> = {
  claude: "seat-claude",
  codex: "seat-codex",
  grok: "seat-grok",
  gemini: "seat-gemini",
  cursor: "seat-cursor",
  mock: "text-muted",
};

export function seatVar(provider: string): string {
  return `var(--at-${SEAT_TOKEN[provider] ?? "text-secondary"})`;
}

export function clock(ts: string): string {
  const d = new Date(ts);
  return Number.isNaN(d.getTime()) ? "" : d.toTimeString().slice(0, 8);
}

const str = (v: unknown): string => (typeof v === "string" ? v : "");

/**
 * @param events the whole journal, in order
 */
export function buildTranscript(events: AutomatonEvent[]): TranscriptRow[] {
  const rows: TranscriptRow[] = [];
  let provider = "mock";
  /** The open assistant row, if a stream is in flight. */
  let streamIndex = -1;

  const closeStream = () => {
    if (streamIndex !== -1) {
      const row = rows[streamIndex];
      if (row.kind === "assistant") row.streaming = false;
      streamIndex = -1;
    }
  };

  for (const e of events) {
    const p = e.payload ?? {};
    switch (e.type) {
      case "session.started":
        provider = str(p.provider) || provider;
        break;

      case "message.user":
        closeStream();
        rows.push({ kind: "user", id: e.id, ts: e.ts, text: str(p.text) });
        break;

      case "message.assistant.delta": {
        const text = str(p.text);
        if (!text) break;
        if (streamIndex === -1) {
          rows.push({
            kind: "assistant",
            id: e.id,
            ts: e.ts,
            provider,
            text,
            streaming: true,
          });
          streamIndex = rows.length - 1;
        } else {
          const row = rows[streamIndex];
          if (row.kind === "assistant") row.text += text;
        }
        break;
      }

      case "message.assistant.final": {
        const text = str(p.text);
        const open = streamIndex === -1 ? null : rows[streamIndex];
        if (open && open.kind === "assistant") {
          // The final usually repeats what was streamed. Prefer it — the vendor's
          // own answer beats anything reassembled from deltas — but never blank
          // out a stream because the final arrived empty.
          if (text) open.text = text;
          open.streaming = false;
          streamIndex = -1;
        } else if (text) {
          rows.push({
            kind: "assistant",
            id: e.id,
            ts: e.ts,
            provider,
            text,
            streaming: false,
          });
        }
        break;
      }

      case "tool.started":
        closeStream();
        rows.push({
          kind: "tool",
          id: e.id,
          ts: e.ts,
          name: str(p.toolName) || "tool",
          state: "started",
        });
        break;

      case "tool.finished": {
        const paths = Array.isArray(p.paths) ? (p.paths as string[]) : [];
        rows.push({
          kind: "tool",
          id: e.id,
          ts: e.ts,
          name: str(p.toolName) || "tool",
          state: "finished",
          detail: paths.length ? paths.join(", ") : undefined,
        });
        break;
      }

      case "artifact.produced": {
        const paths = Array.isArray(p.paths) ? (p.paths as string[]) : [];
        const structured = p.structuredOutput;
        rows.push({
          kind: "artifact",
          id: e.id,
          ts: e.ts,
          text: structured
            ? JSON.stringify(structured, null, 2)
            : paths.join(", ") || "artifact",
        });
        break;
      }

      // A capability the seat could not honour is not a log line to bury: the
      // caller asked for something and did not get it.
      case "capability.degraded":
        rows.push({
          kind: "notice",
          id: e.id,
          ts: e.ts,
          tone: "warn",
          text: `Not available on this seat: ${str(p.label) || str(p.capability)} — ${str(p.reason)}`,
        });
        break;

      case "capability.unsupported":
        rows.push({
          kind: "notice",
          id: e.id,
          ts: e.ts,
          tone: "danger",
          text: `Refused: ${str(p.reason)}`,
        });
        break;

      case "session.failed":
        closeStream();
        rows.push({
          kind: "notice",
          id: e.id,
          ts: e.ts,
          tone: "danger",
          text: `${str(p.errorCode) || "failed"} — ${str(p.errorMessage)}`,
        });
        break;

      case "session.completed":
        closeStream();
        break;

      default:
        break;
    }
  }

  closeStream();
  return rows;
}
