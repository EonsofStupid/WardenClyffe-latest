import { Link, useRouterState } from "@tanstack/react-router";
import type { ReactNode } from "react";
import "../clyffe-code.css";

export function ClyffeCodeShell({ children }: { children: ReactNode }) {
  const path = useRouterState({ select: (s) => s.location.pathname });

  const nav = [
    { to: "/clyffe/code", label: "Workspaces", match: (p: string) => p === "/clyffe/code" },
    { to: "/clyffe/code/order", label: "Order", match: (p: string) => p.startsWith("/clyffe/code/order") },
  ];

  return (
    <div className="cc-shell">
      <header className="cc-top">
        <Link to="/clyffe/code" className="cc-brand">
          <strong>Clyffe Code</strong>
          <span>AI coding cloud · you stay local</span>
        </Link>
        <nav className="cc-nav" aria-label="Clyffe Code">
          {nav.map((n) => (
            <Link key={n.to} to={n.to} data-active={n.match(path) ? "true" : undefined}>
              {n.label}
            </Link>
          ))}
          <span className="cc-mock-badge" title="UI mock for redline — not live provision">
            MOCK UI
          </span>
          <Link to="/">Marketing site</Link>
        </nav>
      </header>
      <main className="cc-main">{children}</main>
    </div>
  );
}
