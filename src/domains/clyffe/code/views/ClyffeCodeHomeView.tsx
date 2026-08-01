import { useEffect, useState } from "react";
import { Link, useNavigate } from "@tanstack/react-router";
import { Button } from "../../../../lib/design";
import { clyffeCodeService } from "../code.svc";
import type { CustomerWorkspace } from "../types";
import { ClyffeCodeShell } from "./ClyffeCodeShell";

function StateLabel({ state }: { state: CustomerWorkspace["state"] }) {
  return (
    <span className="cc-state" data-state={state}>
      {state}
    </span>
  );
}

export function ClyffeCodeHomeView() {
  const nav = useNavigate();
  const [items, setItems] = useState<CustomerWorkspace[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    void clyffeCodeService.list().then((list) => {
      setItems(list);
      setLoading(false);
    });
  }, []);

  return (
    <ClyffeCodeShell>
      <section className="cc-hero">
        <div>
          <p className="cc-mock-badge" style={{ marginBottom: "0.75rem" }}>
            Product mock — redline this
          </p>
          <h1>Cloud container for AI vibe coding</h1>
          <p>
            Work from your machine with Claude, Codex, or Grok on a remote box we sysadmin.
            Double‑click open — SSH and Proxmox stay behind the scenes.
          </p>
        </div>
        <div className="cc-hero-actions">
          <Button
            tone="brand"
            variant="solid"
            onClick={() => void nav({ to: "/clyffe/code/order" })}
          >
            Order a workspace
          </Button>
          <Button
            tone="neutral"
            variant="outline"
            onClick={() => {
              const first = items.find((w) => w.state === "running") ?? items[0];
              if (first) void nav({ to: "/clyffe/code/$workspaceId/open", params: { workspaceId: first.id } });
            }}
          >
            Demo: double‑click open
          </Button>
        </div>
        <div className="cc-pill">
          <i>Local editor / terminal feel</i>
          <i>Remote agents &amp; builds</i>
          <i>Warden runs the infra</i>
          <i>Clyffe is your panel</i>
        </div>
      </section>

      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "baseline", marginBottom: "1rem" }}>
        <h2 style={{ margin: 0, fontSize: "var(--wc-fs-lg)" }}>Your workspaces</h2>
        <Link to="/clyffe/code/order" style={{ fontSize: "var(--wc-fs-sm)", color: "var(--wc-text-dim)" }}>
          + New
        </Link>
      </div>

      {loading && <p className="muted">Loading…</p>}

      <div className="cc-grid">
        {items.map((w) => (
          <article
            key={w.id}
            className="cc-ws-card"
            role="button"
            tabIndex={0}
            onDoubleClick={() => {
              if (w.state === "provisioning") return;
              void nav({ to: "/clyffe/code/$workspaceId/open", params: { workspaceId: w.id } });
            }}
            onClick={() => void nav({ to: "/clyffe/code/$workspaceId", params: { workspaceId: w.id } })}
            onKeyDown={(e) => {
              if (e.key === "Enter") void nav({ to: "/clyffe/code/$workspaceId", params: { workspaceId: w.id } });
            }}
          >
            <div style={{ display: "flex", justifyContent: "space-between", gap: "0.5rem" }}>
              <h2>{w.name}</h2>
              <StateLabel state={w.state} />
            </div>
            <div className="cc-ws-meta">
              <div>
                <strong>{w.tier}</strong> · {w.cores} vCPU · {w.memoryGiB} GiB · {w.diskGiB} GiB
              </div>
              <div>{w.repo ?? "No repo linked yet"}</div>
              <div>
                Connect target: <strong className="mono">{w.connectTarget}</strong>
              </div>
            </div>
            <div className="cc-agents">
              {w.agents.map((a) => (
                <span key={a} className="cc-agent">
                  {a}
                </span>
              ))}
            </div>
            <div className="cc-card-actions">
              <Button
                size="sm"
                tone="brand"
                variant="solid"
                isDisabled={w.state === "provisioning"}
                onClick={(e) => {
                  e.stopPropagation();
                  void nav({ to: "/clyffe/code/$workspaceId/open", params: { workspaceId: w.id } });
                }}
              >
                Open
              </Button>
              <Button
                size="sm"
                tone="neutral"
                variant="ghost"
                onClick={(e) => {
                  e.stopPropagation();
                  void nav({ to: "/clyffe/code/$workspaceId", params: { workspaceId: w.id } });
                }}
              >
                Details
              </Button>
            </div>
            <p className="cc-banner" style={{ margin: 0 }}>
              Tip: double‑click the card to Open (mock Connect).
            </p>
          </article>
        ))}
      </div>

      {!loading && items.length === 0 && (
        <p className="muted">No workspaces yet. Order one to start.</p>
      )}
    </ClyffeCodeShell>
  );
}
