import type { ReactNode } from "react";
import type { Gap } from "./Stack";
import "./layout.css";

export type Justify = "start" | "center" | "end" | "between";

export interface ClusterProps {
  children: ReactNode;
  /** horizontal gap, token scale (default 3) */
  gap?: Gap;
  justify?: Justify;
  align?: "start" | "center" | "end" | "baseline";
  className?: string;
}

/** Cluster — horizontal flex row that wraps (1-D). For toolbars, tag rows,
 *  button groups, key/value chips. */
export function Cluster({ children, gap = 3, justify = "start", align = "center", className }: ClusterProps) {
  return (
    <div
      className={["ui-cluster", className].filter(Boolean).join(" ")}
      data-gap={gap}
      data-justify={justify}
      data-valign={align}
    >
      {children}
    </div>
  );
}
