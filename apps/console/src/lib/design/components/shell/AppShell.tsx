import type { ReactNode } from "react";
import "./shell.css";

export interface AppShellProps {
  sidebar: ReactNode;
  children: ReactNode;
}

/** AppShell — the operator/customer console frame: fixed sidebar + scrolling
 *  main, via CSS Grid. Collapses to single column on narrow viewports. */
export function AppShell({ sidebar, children }: AppShellProps) {
  return (
    <div className="ui-appshell">
      <aside className="ui-appshell__sidebar">{sidebar}</aside>
      <main className="ui-appshell__main">{children}</main>
    </div>
  );
}
