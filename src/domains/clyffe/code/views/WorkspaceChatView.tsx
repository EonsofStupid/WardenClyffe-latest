/**
 * The devspace's chat — the engine's event stream, live, in the browser.
 *
 * This is the first view in Clyffe Code that is **not** mock: `code.svc.ts` is
 * still an in-memory store because the provisioning API does not exist, but the
 * events here come from a real Automaton engine over `automaton serve`, and they
 * are the same events the Zellij pane renders. Nothing is simulated.
 */

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Link, useParams } from "@tanstack/react-router";
import { Button, Card, Field, Select } from "../../../../lib/design";
import { clyffeCodeService } from "../code.svc";
import { engineService, engineTarget, setEngineTarget } from "../engine.svc";
import { CapabilityRefused } from "../engine.types";
import type {
  AutomatonEvent,
  CapabilityGap,
  CapsResponse,
  ProviderReadiness,
  TurnSpec,
} from "../engine.types";
import { buildTranscript, clock, seatVar } from "../engine.transcript";
import { AGENT_LABELS, type AgentReady, type CustomerWorkspace } from "../types";
import { ClyffeCodeShell } from "./ClyffeCodeShell";
import "../automaton-theme.css";

const EFFORTS = ["", "low", "medium", "high"] as const;

export function WorkspaceChatView() {
  const { workspaceId } = useParams({ from: "/clyffe/code/$workspaceId/chat" });

  const [ws, setWs] = useState<CustomerWorkspace | null>(null);
  const [caps, setCaps] = useState<CapsResponse | null>(null);
  const [readiness, setReadiness] = useState<ProviderReadiness[]>([]);
  const [connected, setConnected] = useState<boolean | null>(null);

  const [seat, setSeat] = useState<AgentReady | "mock">("claude");
  const [model, setModel] = useState("");
  const [effort, setEffort] = useState("");
  const [prompt, setPrompt] = useState("");

  const [events, setEvents] = useState<AutomatonEvent[]>([]);
  const [gaps, setGaps] = useState<CapabilityGap[]>([]);
  const [refused, setRefused] = useState<CapabilityGap[] | null>(null);
  const [busy, setBusy] = useState(false);
  const [err, setErr] = useState<string | null>(null);

  const abort = useRef<AbortController | null>(null);
  const logEnd = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    clyffeCodeService.get(workspaceId).then(setWs);
  }, [workspaceId]);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      const ok = await engineService.health();
      if (cancelled) return;
      setConnected(ok);
      if (!ok) return;
      try {
        const [c, p] = await Promise.all([engineService.caps(), engineService.providers()]);
        if (cancelled) return;
        setCaps(c);
        setReadiness(p);
      } catch (e) {
        if (!cancelled) setErr(e instanceof Error ? e.message : String(e));
      }
    })();
    return () => {
      cancelled = true;
    };
  }, []);

  // Stop the stream if the operator navigates away mid-turn.
  useEffect(() => () => abort.current?.abort(), []);

  const transcript = useMemo(() => buildTranscript(events), [events]);

  useEffect(() => {
    logEnd.current?.scrollIntoView({ block: "end" });
  }, [transcript.length, events.length]);

  /** Seats this workspace bought, that the engine actually has an adapter for. */
  const seats = useMemo(() => {
    const offered = ws?.agents ?? [];
    const known = new Set(caps?.seats ?? []);
    const rows = offered.filter((a) => known.size === 0 || known.has(a));
    return rows.length ? rows : offered;
  }, [ws, caps]);

  useEffect(() => {
    if (seats.length && !seats.includes(seat as AgentReady)) setSeat(seats[0]);
  }, [seats, seat]);

  const seatState = (id: string) => readiness.find((r) => r.provider === id)?.auth.state;

  /** Which knobs to show at all — asking for one the seat lacks is a refusal. */
  const supports = useCallback(
    (capability: string) => {
      const row = caps?.matrix.find((r) => r.capability === capability);
      return row ? row.seats[seat] != null : true;
    },
    [caps, seat]
  );

  async function send() {
    const text = prompt.trim();
    if (!text || busy) return;

    const spec: TurnSpec = {};
    if (model.trim() && supports("model")) spec.model = model.trim();
    if (effort && supports("effort")) spec.effort = effort as TurnSpec["effort"];

    setBusy(true);
    setErr(null);
    setRefused(null);
    setGaps([]);
    setPrompt("");

    const controller = new AbortController();
    abort.current = controller;

    try {
      await engineService.startTurn(
        { provider: seat, prompt: text, spec },
        {
          onDegraded: (gap) => setGaps((g) => [...g, gap]),
          onEvent: (event) => setEvents((prev) => [...prev, event]),
        },
        controller.signal
      );
    } catch (e) {
      if (e instanceof CapabilityRefused) {
        // The engine refused before spawning, so nothing ran. Say what was asked
        // for and not delivered, rather than a generic failure.
        setRefused(e.blocking);
      } else if (!controller.signal.aborted) {
        setErr(e instanceof Error ? e.message : String(e));
      }
    } finally {
      abort.current = null;
      setBusy(false);
    }
  }

  if (connected === false) {
    return (
      <ClyffeCodeShell>
        <EngineSetup onSaved={() => window.location.reload()} />
      </ClyffeCodeShell>
    );
  }

  return (
    <ClyffeCodeShell>
      <div className="cc-detail-head">
        <div>
          <h1>{ws?.name ?? "Workspace"}</h1>
          <p className="muted">
            Live engine · the same event stream the terminal pane renders
          </p>
        </div>
        <Link to="/clyffe/code/$workspaceId" params={{ workspaceId }}>
          <Button variant="ghost" size="sm">
            Back to workspace
          </Button>
        </Link>
      </div>

      <Card title="Seat">
        <div className="cc-chat-controls">
          <Select
            label="Agent"
            selectedKey={seat}
            onSelect={(k) => setSeat(k as AgentReady)}
            options={seats.map((id) => ({
              id,
              label: `${AGENT_LABELS[id] ?? id}${seatState(id) === "green" ? "" : " · not ready"}`,
            }))}
          />
          {supports("model") ? (
            <Field label="Model" value={model} onChange={setModel} placeholder="seat default" />
          ) : null}
          {supports("effort") ? (
            <Select
              label="Effort"
              selectedKey={effort}
              onSelect={setEffort}
              options={EFFORTS.map((e) => ({ id: e, label: e || "seat default" }))}
            />
          ) : null}
        </div>
        <p className="muted cc-chat-hint">
          Only the controls this seat can honour are shown. The engine refuses a
          request it cannot deliver rather than dropping it.
        </p>
      </Card>

      <Card title="Session">
        <div className="cc-log" role="log" aria-live="polite">
          {transcript.length === 0 ? (
            <p className="muted">Nothing yet. Ask the seat something.</p>
          ) : null}

          {transcript.map((row) => {
            if (row.kind === "user") {
              return (
                <div key={row.id} className="cc-log-row">
                  <span className="cc-log-ts">{clock(row.ts)}</span>
                  <span className="cc-log-who cc-log-who--you">you</span>
                  <span className="cc-log-text">{row.text}</span>
                </div>
              );
            }
            if (row.kind === "assistant") {
              return (
                <div key={row.id} className="cc-log-row">
                  <span className="cc-log-ts">{clock(row.ts)}</span>
                  <span className="cc-log-who" style={{ color: seatVar(row.provider) }}>
                    {AGENT_LABELS[row.provider as AgentReady] ?? row.provider}
                  </span>
                  <span className="cc-log-text">
                    {row.text}
                    {row.streaming ? <span className="cc-cursor" aria-hidden /> : null}
                  </span>
                </div>
              );
            }
            if (row.kind === "tool") {
              return (
                <div key={row.id} className="cc-log-row cc-log-row--dim">
                  <span className="cc-log-ts">{clock(row.ts)}</span>
                  <span className="cc-log-who cc-log-who--tool">{row.state === "started" ? "⋯" : "✓"}</span>
                  <span className="cc-log-text">
                    {row.name}
                    {row.detail ? ` · ${row.detail}` : ""}
                  </span>
                </div>
              );
            }
            if (row.kind === "artifact") {
              return (
                <div key={row.id} className="cc-log-row">
                  <span className="cc-log-ts">{clock(row.ts)}</span>
                  <span className="cc-log-who cc-log-who--tool">out</span>
                  <pre className="cc-log-pre">{row.text}</pre>
                </div>
              );
            }
            return (
              <div key={row.id} className="cc-log-row" data-tone={row.tone}>
                <span className="cc-log-ts">{clock(row.ts)}</span>
                <span className="cc-log-who cc-log-who--notice">!</span>
                <span className="cc-log-text">{row.text}</span>
              </div>
            );
          })}
          <div ref={logEnd} />
        </div>

        {gaps.length ? (
          <ul className="cc-gaps">
            {gaps.map((g) => (
              <li key={g.capability}>
                <strong>{g.label}</strong> — {g.reason}
              </li>
            ))}
          </ul>
        ) : null}

        {refused ? (
          <div className="cc-refused" role="alert">
            <strong>Nothing ran.</strong> This seat cannot honour the request:
            <ul>
              {refused.map((g) => (
                <li key={g.capability}>{g.reason}</li>
              ))}
            </ul>
          </div>
        ) : null}

        {err ? (
          <p className="cc-error" role="alert">
            {err}
          </p>
        ) : null}

        <form
          className="cc-composer"
          onSubmit={(e) => {
            e.preventDefault();
            void send();
          }}
        >
          <textarea
            className="cc-composer-input"
            value={prompt}
            onChange={(e) => setPrompt(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === "Enter" && (e.metaKey || e.ctrlKey)) {
                e.preventDefault();
                void send();
              }
            }}
            rows={3}
            placeholder={busy ? "Working…" : "Ask the seat something. ⌘/Ctrl+Enter to send."}
            disabled={busy}
          />
          <div className="cc-composer-actions">
            <Button type="submit" isDisabled={busy || !prompt.trim()}>
              {busy ? "Running…" : "Send"}
            </Button>
            {busy ? (
              <Button variant="ghost" tone="danger" onPress={() => abort.current?.abort()}>
                Stop reading
              </Button>
            ) : null}
          </div>
        </form>
        {busy ? (
          <p className="muted cc-chat-hint">
            “Stop reading” closes this stream. The turn keeps running on the box —
            the engine has no cancel yet, and pretending otherwise would be a lie.
          </p>
        ) : null}
      </Card>
    </ClyffeCodeShell>
  );
}

/**
 * No engine configured. Deliberately explicit: until the control plane issues a
 * per-workspace endpoint and a scoped token, someone has to say where the engine
 * is, and a devspace that silently showed an empty chat would be worse.
 */
function EngineSetup({ onSaved }: { onSaved: () => void }) {
  const existing = engineTarget();
  const [url, setUrl] = useState(existing?.url ?? "http://127.0.0.1:8100");
  const [token, setToken] = useState(existing?.token ?? "");

  return (
    <Card title="Connect an engine">
      <p className="muted" style={{ marginTop: 0 }}>
        This view streams events from a running Automaton engine. Start one in the
        workspace and paste the token it prints:
      </p>
      <pre className="cc-log-pre">automaton serve --workspace . --origin {location.origin}</pre>
      <div className="cc-chat-controls">
        <Field label="Engine URL" value={url} onChange={setUrl} />
        <Field label="Token" value={token} onChange={setToken} type="password" />
      </div>
      <div className="cc-composer-actions">
        <Button
          isDisabled={!url.trim() || !token.trim()}
          onPress={() => {
            setEngineTarget({ url: url.trim(), token: token.trim() });
            onSaved();
          }}
        >
          Connect
        </Button>
      </div>
      <p className="muted cc-chat-hint">
        Stored in this browser only. A socket that can start an agent session can
        run code in the workspace — the engine binds loopback and refuses a
        routable address unless it is told to twice.
      </p>
    </Card>
  );
}
