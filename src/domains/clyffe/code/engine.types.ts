/**
 * The Automaton engine's wire shapes, mirrored.
 *
 * These are not a web model of a session — they are the engine's `AutomatonEvent`
 * and `TurnSpec` verbatim, because the whole point of `automaton serve` is that
 * the browser reads the *same* journal the pane does. If these drift, the
 * devspace is showing something the engine never said. Source of truth:
 * `automaton/engine/abstract/types.mjs` and `engine/abstract/capabilities.mjs`.
 */

import type { AgentReady } from "./types";

export type AutomatonEvent = {
  v: 0;
  id: string;
  sessionId: string;
  ts: string;
  seq?: number;
  type: string;
  payload?: Record<string, unknown>;
};

export type SessionStatus =
  | "idle"
  | "running"
  | "waiting_permission"
  | "checking"
  | "failed"
  | "completed"
  | "cancelled";

export type EngineSession = {
  v: 0;
  id: string;
  provider: AgentReady | "mock";
  status: SessionStatus;
  createdAt: string;
  updatedAt?: string;
  errorCode?: string;
  errorMessage?: string;
  live?: boolean;
};

/**
 * What a turn asks for, as opposed to what the operator allows. The engine
 * refuses a spec the chosen seat cannot honour rather than dropping it, so this
 * is a contract the UI can rely on: if the POST returns 200, everything here is
 * being delivered.
 */
export type TurnSpec = {
  model?: string;
  effort?: "low" | "medium" | "high" | "xhigh" | "max";
  permissionMode?: string;
  sandbox?: "read" | "workspace_write" | "full";
  toolAllow?: string[];
  toolDeny?: string[];
  systemPrompt?: { mode: "append" | "replace"; text: string };
  structuredOutput?: Record<string, unknown>;
  addDirs?: string[];
  maxTurns?: number;
  budgetUsd?: number;
};

/** Why an unsupported capability is a refusal or only a note. */
export type Bearing =
  | "safety"
  | "contract"
  | "quality"
  | "behaviour"
  | "input"
  | "continuity"
  | "budget"
  | "observability"
  | "transport";

export type CapabilityRow = {
  capability: string;
  label: string;
  bearing: Bearing;
  supported: boolean;
  via: string | null;
  values: string[] | null;
  note: string | null;
};

export type CapabilityMatrixRow = {
  capability: string;
  bearing: Bearing;
  label: string;
  /** seat id → how it is delivered, or null when the seat cannot */
  seats: Record<string, string | null>;
};

export type CapsResponse = {
  bearings: Record<string, { bearing: Bearing; label: string }>;
  seats: string[];
  matrix: CapabilityMatrixRow[];
};

export type CapabilityGap = {
  capability: string;
  bearing: Bearing;
  label: string;
  requested: unknown;
  reason: string;
  blocking: boolean;
};

export type ProviderReadiness = {
  provider: string;
  health: "ok" | "degraded" | "missing";
  binary: { present: boolean; path?: string; version?: string | null };
  auth: { state: string; accessClass: string; summary?: string };
  probeMs: number;
};

/** Raised when the engine refuses a spec (HTTP 422), carrying the whole list. */
export class CapabilityRefused extends Error {
  blocking: CapabilityGap[];
  degraded: CapabilityGap[];

  constructor(blocking: CapabilityGap[], degraded: CapabilityGap[]) {
    super(blocking.map((b) => b.reason).join("; ") || "capability unsupported");
    this.name = "CapabilityRefused";
    this.blocking = blocking;
    this.degraded = degraded;
  }
}
