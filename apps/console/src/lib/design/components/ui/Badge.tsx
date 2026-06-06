import { cx, type Tone } from "../../utils/cx";
import "./Badge.css";

const STATE_TONE: Record<string, Tone> = {
  running: "success", reachable: "success", ok: "success",
  requested: "warning", planned: "warning", provisioning: "warning",
  error: "danger", down: "danger", failed: "danger", legacy: "danger",
  stopped: "neutral", destroyed: "neutral", unknown: "neutral",
};

export interface BadgeProps {
  children?: React.ReactNode;
  tone?: Tone;
  /** When set, derives tone from a resource/service state string. */
  state?: string;
  dot?: boolean;
}

/** Badge — small status pill. Tone derived from `state` or passed directly. */
export function Badge({ children, tone, state, dot = true }: BadgeProps) {
  const resolved: Tone = tone ?? (state ? STATE_TONE[state] ?? "neutral" : "neutral");
  return (
    <span className={cx("ui-badge", dot && "ui-badge--dot")} data-tone={resolved}>
      {children ?? state}
    </span>
  );
}
