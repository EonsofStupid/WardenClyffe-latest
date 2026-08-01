import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "@tanstack/react-router";
import { Button, Card } from "../../../../lib/design";
import { clyffeCodeService } from "../code.svc";
import type { CustomerWorkspace } from "../types";
import { ClyffeCodeShell } from "./ClyffeCodeShell";

export function WorkspaceDetailView() {
  const { workspaceId } = useParams({ from: "/clyffe/code/$workspaceId" });
  const nav = useNavigate();
  const [ws, setWs] = useState<CustomerWorkspace | null>(null);
  const [err, setErr] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  const reload = () =>
    clyffeCodeService.get(workspaceId).then((w) => {
      if (!w) setErr("Workspace not found");
      else setWs(w);
    });

  useEffect(() => {
    void reload();
  }, [workspaceId]);

  if (err) {
    return (
      <ClyffeCodeShell>
        <p>{err}</p>
        <Link to="/clyffe/code">Back</Link>
      </ClyffeCodeShell>
    );
  }
  if (!ws) {
    return (
      <ClyffeCodeShell>
        <p className="muted">Loading…</p>
      </ClyffeCodeShell>
    );
  }

  return (
    <ClyffeCodeShell>
      <div className="cc-detail-hero">
        <div>
          <Link to="/clyffe/code" style={{ fontSize: "var(--wc-fs-sm)", color: "var(--wc-text-dim)" }}>
            ← Workspaces
          </Link>
          <h1 style={{ margin: "0.5rem 0 0.25rem", fontSize: "var(--wc-fs-xl)" }}>{ws.name}</h1>
          <p className="muted" style={{ margin: 0 }}>
            <span className="cc-state" data-state={ws.state}>
              {ws.state}
            </span>
            {" · "}
            {ws.tier} · {ws.region}
          </p>
        </div>
        <div className="cc-hero-actions">
          <Button
            tone="brand"
            variant="solid"
            isDisabled={ws.state === "provisioning"}
            onClick={() =>
              void nav({ to: "/clyffe/code/$workspaceId/open", params: { workspaceId: ws.id } })
            }
          >
            Open (double‑click path)
          </Button>
          {ws.state === "stopped" && (
            <Button
              tone="neutral"
              variant="outline"
              isDisabled={busy}
              onClick={async () => {
                setBusy(true);
                await clyffeCodeService.start(ws.id);
                await reload();
                setBusy(false);
              }}
            >
              Start
            </Button>
          )}
          {ws.state === "running" && (
            <Button
              tone="neutral"
              variant="outline"
              isDisabled={busy}
              onClick={async () => {
                setBusy(true);
                await clyffeCodeService.stop(ws.id);
                await reload();
                setBusy(false);
              }}
            >
              Stop
            </Button>
          )}
        </div>
      </div>

      <div className="cc-grid">
        <Card title="What you get">
          <dl className="kv">
            <dt>Resources</dt>
            <dd>
              {ws.cores} vCPU · {ws.memoryGiB} GiB · {ws.diskGiB} GiB
            </dd>
            <dt>Repo</dt>
            <dd className="mono">{ws.repo ?? "—"}</dd>
            <dt>Connect target</dt>
            <dd className="mono">{ws.connectTarget}</dd>
            <dt>Last opened</dt>
            <dd>{ws.lastOpenedAt ? new Date(ws.lastOpenedAt).toLocaleString() : "Never"}</dd>
            <dt>Preview</dt>
            <dd>{ws.previewHint ?? "—"}</dd>
          </dl>
        </Card>

        <Card title="AI on the box">
          <p className="muted" style={{ marginTop: 0 }}>
            Agents run on the remote workspace. Your laptop is the glass.
          </p>
          <div className="cc-agents">
            {ws.agents.map((a) => (
              <span key={a} className="cc-agent">
                {a}
              </span>
            ))}
          </div>
        </Card>

        <Card title="What you never manage">
          <ul style={{ margin: 0, paddingLeft: "1.1rem", color: "var(--wc-text-dim)", fontSize: "var(--wc-fs-sm)" }}>
            <li>SSH keys &amp; jump hosts</li>
            <li>Proxmox / VM networking</li>
            <li>Package bootstrap &amp; agent installs</li>
            <li>Secrets plumbing (brokered for you)</li>
          </ul>
        </Card>
      </div>

      <p className="cc-banner" style={{ marginTop: "1.5rem" }}>
        Redline note: Open is mocked in-browser. Production path is Clyffe Connect / custom terminal →
        private path → <code>customer-devstation</code> via Warden.
      </p>
    </ClyffeCodeShell>
  );
}
