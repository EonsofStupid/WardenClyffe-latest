import type { ReactNode } from "react";
import { Link, useRouterState } from "@tanstack/react-router";
import { AppShell, Sidebar, NavItem } from "../../../lib/design";

const NAV: { to: string; label: string; exact?: boolean }[] = [
  { to: "/admin", label: "Home", exact: true },
  { to: "/admin/proxmox", label: "Proxmox fleet" },
  { to: "/admin/workspaces", label: "Workspaces" },
  { to: "/admin/order", label: "Order devstation" },
  { to: "/admin/data", label: "Data browser" },
  { to: "/admin/control", label: "Control layer" },
  { to: "/admin/intelligence", label: "Intelligence" },
  { to: "/admin/connect", label: "Connect & Launch" },
  { to: "/admin/edge", label: "Public IPs" },
  { to: "/warden", label: "Overview" },
];

/** Shared operator chrome for admin screens. */
export function AdminShell({ children }: { children: ReactNode }) {
  const path = useRouterState({ select: (s) => s.location.pathname });

  return (
    <AppShell
      sidebar={
        <Sidebar
          brand={
            <>
              WardenClyffe
              <small>operator panel</small>
            </>
          }
        >
          {NAV.map((n) => {
            const active = n.exact ? path === n.to : path === n.to || path.startsWith(n.to + "/");
            return (
              <NavItem
                key={n.to}
                active={active}
                onPress={() => {
                  window.location.href = n.to;
                }}
              >
                {n.label}
              </NavItem>
            );
          })}
          <div style={{ marginTop: "0.5rem" }}>
            <Link to="/clyffy" style={{ fontSize: "var(--wc-fs-xs)", color: "var(--wc-text-dim)" }}>
              Clyffy
            </Link>
          </div>
        </Sidebar>
      }
    >
      {children}
    </AppShell>
  );
}
