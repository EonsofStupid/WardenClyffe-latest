import { useEffect, useState } from "react";
import { api } from "../lib/api";
import { Workspaces } from "../views/Workspaces";
import { DataBrowser } from "../views/DataBrowser";
import { OrderDevstation } from "../views/OrderDevstation";
import { Foundation } from "../views/Foundation";
import { AppShell, Sidebar, NavItem, Badge } from "../lib/design";

type Tab = "foundation" | "workspaces" | "data" | "order";
const NAV: { id: Tab; label: string }[] = [
  { id: "foundation", label: "Foundation" },
  { id: "workspaces", label: "Workspaces" },
  { id: "data", label: "Data" },
  { id: "order", label: "Order Devstation" },
];

function initialTab(): Tab {
  const h = window.location.hash.replace("#", "") as Tab;
  return ["foundation", "workspaces", "data", "order"].includes(h) ? h : "foundation";
}

export function App() {
  const [tab, setTabState] = useState<Tab>(initialTab());
  const setTab = (t: Tab) => {
    setTabState(t);
    window.location.hash = t;
  };
  const [health, setHealth] = useState<"ok" | "down" | "…">("…");

  useEffect(() => {
    const ping = () => api.health().then(() => setHealth("ok")).catch(() => setHealth("down"));
    ping();
    const t = setInterval(ping, 5000);
    return () => clearInterval(t);
  }, []);

  const sidebar = (
    <Sidebar
      brand="WardenClyffe"
      sub="operator console"
      footer={
        <Badge tone={health === "ok" ? "success" : health === "down" ? "danger" : "neutral"}>
          warden-api {health}
        </Badge>
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
      {tab === "foundation" && <Foundation />}
      {tab === "workspaces" && <Workspaces />}
      {tab === "data" && <DataBrowser />}
      {tab === "order" && <OrderDevstation />}
    </AppShell>
  );
}
