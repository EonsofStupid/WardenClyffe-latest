import type { ReactNode } from "react";
import "./shell.css";

export interface SidebarProps {
  brand: ReactNode;
  sub?: ReactNode;
  children: ReactNode;
  footer?: ReactNode;
}

/** Sidebar — brand + nav region inside AppShell. Footer pins to the bottom. */
export function Sidebar({ brand, sub, children, footer }: SidebarProps) {
  return (
    <div className="ui-sidebar">
      <div className="ui-sidebar__brand">
        {brand}
        {sub && <small>{sub}</small>}
      </div>
      <nav className="ui-sidebar__nav" aria-label="Primary">
        {children}
      </nav>
      {footer && <div className="ui-sidebar__footer">{footer}</div>}
    </div>
  );
}
