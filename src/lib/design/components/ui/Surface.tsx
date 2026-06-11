import type { ReactNode } from "react";
import "./Surface.css";

/** Coldlight surface treatments. The single source of "how a panel sits":
 *  flat (recessed page), raised (floating), embossed (lit-from-top, proud),
 *  glass (morphism), sunken (pressed-in). */
export type SurfaceVariant = "flat" | "raised" | "embossed" | "glass" | "sunken";

export interface SurfaceProps {
  children: ReactNode;
  variant?: SurfaceVariant;
  className?: string;
}

/** Surface — the base coldlight panel. Domains compose this (or Card) into
 *  colocated variants; they never re-implement the treatment. */
export function Surface({ children, variant = "raised", className }: SurfaceProps) {
  return (
    <div className={["ui-surface", className].filter(Boolean).join(" ")} data-surface={variant}>
      {children}
    </div>
  );
}
