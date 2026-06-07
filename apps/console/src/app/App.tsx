import { useEffect, useState } from "react";
import { api } from "../lib/api";
import { AppShell, Sidebar, NavItem, Badge, Button, Cluster } from "../lib/design";
import { AuthProvider, useAuth, LoginView } from "../domains/warden/identity";
import { OverviewView } from "../domains/warden/overview";
import { FoundationView } from "../domains/warden/mesh";
import { WorkspacesView, OrderDevstationView } from "../domains/warden/fleet";
import { DataBrowserView } from "../domains/warden/data";

type Tab = "overview" | "foundation" | "workspaces" | "data" | "order";
const NAV: { id: Tab; label: string }[] = [
  { id: "overview", label: "Overview" },
  { id: "foundation", label: "Foundation" },
  { id: "workspaces", label: "Workspaces" },
  { id: "data", label: "Data" },
  { id: "order", label: "Order Devstation" },
];
const TABS = NAV.map((n) => n.id);

function initialTab(): Tab {
  const h = window.location.hash.replace("#", "") as Tab;
  return TABS.includes(h) ? h : "overview";
}

function Console() {
  const { operator, loading, logout } = useAuth();
  const [tab, setTabState] = useState<Tab>(initialTab());
  const setTab = (t: Tab) => {
    setTabState(t);
    window.location.hash = t;
  };
  const [health, setHealth] = useState<"ok" | "down" | "…">("…");

  useEffect(() => {
    if (!operator) return;
    const ping = () => api.health().then(() => setHealth("ok")).catch(() => setHealth("down"));
    ping();
    const t = setInterval(ping, 5000);
    return () => clearInterval(t);
  }, [operator]);

  if (loading) {
    return <div style={{ display: "grid", placeItems: "center", height: "100%" }}>…</div>;
  }
  if (!operator) {
    return <LoginView />;
  }

  const sidebar = (
    <Sidebar
      brand="WardenClyffe"
      sub="operator console"
      footer={
        <Cluster gap={2} justify="between">
          <Badge tone={health === "ok" ? "success" : health === "down" ? "danger" : "neutral"}>
            warden-api {health}
          </Badge>
          <Button size="sm" tone="neutral" variant="ghost" onPress={() => void logout()}>
            Sign out
          </Button>
        </Cluster>
      }
    >
      {NAV.map((n) => (
        <NavItem key={n.id} active={tab === n.id} onPress={() => setTab(n.id)}>
          {n.label}
        </NavItem>
      ))}
    </Sidebar>
  );

  return (
    <AppShell sidebar={sidebar}>
      {tab === "overview" && <OverviewView />}
      {tab === "foundation" && <FoundationView />}
      {tab === "workspaces" && <WorkspacesView />}
      {tab === "data" && <DataBrowserView />}
      {tab === "order" && <OrderDevstationView />}
    </AppShell>
  );
}

export function App() {
  return (
    <AuthProvider>
      <Console />
    </AuthProvider>
  );
}
