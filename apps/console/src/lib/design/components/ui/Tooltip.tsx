import { TooltipTrigger, Tooltip as AriaTooltip } from "react-aria-components";
import type { ReactNode } from "react";
import "./Tooltip.css";

export interface TooltipProps {
  content: ReactNode;
  /** the element the tooltip describes (must be focusable, e.g. a Button) */
  children: ReactNode;
}

/** Tooltip — RAC tooltip on hover/focus. */
export function Tooltip({ content, children }: TooltipProps) {
  return (
    <TooltipTrigger delay={400}>
      {children}
      <AriaTooltip className="ui-tooltip" offset={6}>
        {content}
      </AriaTooltip>
    </TooltipTrigger>
  );
}
