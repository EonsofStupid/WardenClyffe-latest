import type { ReactNode } from "react";
import "./layout.css";

/** Token-scale gap (maps to --wc-s1..s7). Shared by Stack/Cluster/Grid. */
export type Gap = 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7;
export type Align = "start" | "center" | "end" | "stretch";

export interface StackProps {
  children: ReactNode;
  /** vertical gap, token scale (default 4 = 1rem) */
  gap?: Gap;
  align?: Align;
  className?: string;
}

/** Stack — vertical flex layout (1-D). The default for stacking content. */
export function Stack({ children, gap = 4, align = "stretch", className }: StackProps) {
  return (
    <div className={["ui-stack", className].filter(Boolean).join(" ")} data-gap={gap} data-align={align}>
      {children}
    </div>
  );
}
