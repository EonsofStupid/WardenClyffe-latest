import { useEffect, useState } from "react";
import { api } from "../lib/api";
import { Workspaces } from "../views/Workspaces";
import { DataBrowser } from "../views/DataBrowser";
import { OrderDevstation } from "../views/OrderDevstation";
import { Foundation } from "../views/Foundation";
import { Badge } from "../lib/design";

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
  const setTab = (t: Tab) => { setTabState(t); window.location.hash = t; };
  const [health, setHealth] = useState<"ok" | "down" | "…">("…");

  useEffect(() => {
    const ping = () => api.health().then(() => setHealth("ok")).catch(() => setHealth("down"));
    ping();
    const t = setInterval(ping, 5000);
    return () => clearInterval(t);
  }, []);

  return (
    <div className="app">
      <aside className="sidebar">
        <div className="brand">WardenClyffe<small>operator console</small></div>
        {NAV.map((n) => (
          <button key={n.id} className="navitem" data-active={tab === n.id} onClick={() => setTab(n.id)}>{n.label}</button>
        ))}
        <div style={{ marginTop: "auto" }} className="row">
          <Badge tone={health === "ok" ? "success" : health === "down" ? "danger" : "neutral"}>warden-api {health}</Badge>
        </div>
      </aside>
      <main className="main">
        {tab === "foundation" && <Foundation />}
        {tab === "workspaces" && <Workspaces />}
        {tab === "data" && <DataBrowser />}
        {tab === "order" && <OrderDevstation />}
      </main>
    </div>
  );
}
