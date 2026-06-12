import type { CSSProperties, ReactNode } from "react";
import type { Gap } from "./Stack";
import "./layout.css";

export interface GridProps {
  children: ReactNode;
  /** gap, token scale (default 4) */
  gap?: Gap;
  /** min column width in rem before wrapping (auto-fill) */
  min?: number;
  className?: string;
}

/** Grid — responsive CSS Grid (2-D) with auto-fill columns. For card decks and
 *  dashboards. Use Stack/Cluster for 1-D alignment inside cells. */
export function Grid({ children, gap = 4, min = 16, className }: GridProps) {
  const style = { "--ui-grid-min": `${min}rem` } as CSSProperties;
  return (
    <div className={["ui-grid", className].filter(Boolean).join(" ")} data-gap={gap} style={style}>
      {children}
    </div>
  );
}
