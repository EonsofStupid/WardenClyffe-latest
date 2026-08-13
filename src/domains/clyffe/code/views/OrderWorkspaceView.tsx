import { useState, type FormEvent } from "react";
import { Link, useNavigate } from "@tanstack/react-router";
import { Button, Card, Field, Select } from "../../../../lib/design";
import { clyffeCodeService } from "../code.svc";
import { TIER_OPTIONS } from "../mock-data";
import { AGENT_LABELS, ALL_AGENTS, type AgentReady, type WorkspaceTier } from "../types";
import { ClyffeCodeShell } from "./ClyffeCodeShell";

// Derived from the seat list rather than typed out again, so a seat can only be
// offered here if the engine actually has an adapter for it.
const AGENTS: { id: AgentReady; label: string }[] = ALL_AGENTS.map((id) => ({
  id,
  label: AGENT_LABELS[id],
}));

export function OrderWorkspaceView() {
  const nav = useNavigate();
  const [name, setName] = useState("My vibe box");
  const [tier, setTier] = useState<WorkspaceTier>("builder");
  const [repo, setRepo] = useState("");
  const [region, setRegion] = useState("us-east");
  const [agents, setAgents] = useState<AgentReady[]>([...ALL_AGENTS]);
  const [busy, setBusy] = useState(false);
  const [err, setErr] = useState<string | null>(null);

  const toggleAgent = (id: AgentReady) => {
    setAgents((prev) => (prev.includes(id) ? prev.filter((a) => a !== id) : [...prev, id]));
  };

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setBusy(true);
    setErr(null);
    try {
      const ws = await clyffeCodeService.order({
        name,
        tier,
        repo: repo || undefined,
        region,
        agents,
      });
      void nav({ to: "/clyffe/code/$workspaceId", params: { workspaceId: ws.id } });
    } catch (ex) {
      setErr(ex instanceof Error ? ex.message : String(ex));
    } finally {
      setBusy(false);
    }
  }

  return (
    <ClyffeCodeShell>
      <Link to="/clyffe/code" style={{ fontSize: "var(--wc-fs-sm)", color: "var(--wc-text-dim)" }}>
        ← Workspaces
      </Link>
      <h1 style={{ margin: "0.75rem 0", fontSize: "var(--wc-fs-xl)" }}>Order a coding cloud</h1>
      <p className="muted" style={{ maxWidth: "36rem" }}>
        Pick a tier. We provision a clean customer template (not the operator box). You open it from
        your machine — we do the sysadmin.
      </p>

      <form className="cc-form" onSubmit={onSubmit} style={{ marginTop: "1.5rem" }}>
        <Card title="Workspace">
          <div style={{ display: "grid", gap: "1rem" }}>
            <Field label="Name" value={name} onChange={setName} placeholder="My vibe box" />
            <Select
              label="Tier"
              selectedKey={tier}
              onSelect={(k) => setTier(k as WorkspaceTier)}
              options={TIER_OPTIONS.map((t) => ({ id: t.id, label: `${t.label} — ${t.blurb}` }))}
            />
            <Field
              label="Repo (optional)"
              value={repo}
              onChange={setRepo}
              placeholder="github.com/you/project"
            />
            <Field label="Region" value={region} onChange={setRegion} placeholder="us-east" />
            <div>
              <div style={{ fontSize: "var(--wc-fs-sm)", marginBottom: "0.5rem" }}>Agents on the box</div>
              <div className="cc-checkrow">
                {AGENTS.map((a) => (
                  <label key={a.id}>
                    <input
                      type="checkbox"
                      checked={agents.includes(a.id)}
                      onChange={() => toggleAgent(a.id)}
                    />
                    {a.label}
                  </label>
                ))}
              </div>
            </div>
            <Button type="submit" tone="brand" variant="solid" isDisabled={busy}>
              {busy ? "Ordering…" : "Order workspace"}
            </Button>
          </div>
        </Card>
        {err && (
          <p role="alert" style={{ color: "oklch(72% 0.16 25)" }}>
            {err}
          </p>
        )}
      </form>

      <p className="cc-banner" style={{ marginTop: "1.5rem" }}>
        Mock order only — records stay in browser memory until refresh. Wire later: Warden provision
        task + Clyffe workspace row.
      </p>
    </ClyffeCodeShell>
  );
}
