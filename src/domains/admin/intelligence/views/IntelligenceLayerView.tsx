// Admin · intelligence layer — touchpoint inventory health + projection plan,
// with operator-gated sync trigger. Dumb view: all data and the run action go
// through meshService (Go drives).
import { useEffect, useState } from "react";
import { Button, Card } from "../../../../lib/design";
import { meshService } from "../../../warden/mesh/mesh.svc";
import type { IntelligenceInventory, ProjectionPlan } from "../../../warden/mesh/types";

export function IntelligenceLayerView() {
  const [inv, setInv] = useState<IntelligenceInventory | null>(null);
  const [plan, setPlan] = useState<ProjectionPlan | null>(null);
  const [planPresent, setPlanPresent] = useState<boolean | null>(null);
  const [busy, setBusy] = useState(false);
  const [err, setErr] = useState<string | null>(null);

  const loadProjection = () =>
    meshService
      .projection()
      .then((r) => {
        setPlan(r.plan);
        setPlanPresent(r.present);
      })
      .catch((e) => setErr((e as Error).message));

  useEffect(() => {
    meshService.intelligence().then(setInv).catch((e) => setErr((e as Error).message));
    void loadProjection();
  }, []);

  const runSync = () => {
    setBusy(true);
    setErr(null);
    meshService
      .runSync()
      .then((r) => {
        setPlan(r.plan);
        setPlanPresent(true);
      })
      .catch((e) => setErr((e as Error).message))
      .finally(() => setBusy(false));
  };

  const s = plan?.summary;

  return (
    <div>
      <h1 className="page-title">Intelligence layer</h1>
      <p className="page-sub">Touchpoint inventory + projection plan — what routes, what projects, what drifts.</p>
      {err && <Card title="Error"><span className="muted">{err}</span></Card>}

      {inv && (
        <Card title="Inventory">
          <dl className="kv">
            <dt>touchpoints</dt><dd>{inv.summary.total}</dd>
            <dt>v2</dt><dd>{inv.summary.v2}</dd>
            <dt>v1 (deprecated)</dt><dd>{inv.summary.v1_deprecated}</dd>
            <dt>sync-enabled</dt><dd>{inv.summary.sync_enabled}</dd>
            <dt>with warnings</dt><dd>{inv.summary.with_warnings}</dd>
          </dl>
        </Card>
      )}

      <Card
        title="Projection plan"
        action={
          <Button size="sm" tone="brand" variant="solid" isDisabled={busy} onPress={runSync}>
            {busy ? "Running sync…" : "Run sync"}
          </Button>
        }
      >
        {planPresent === false && <span className="muted">No projection yet — run a sync to build one.</span>}
        {s && (
          <dl className="kv">
            <dt>total</dt><dd>{s.Total ?? 0}</dd>
            <dt>new</dt><dd>{s.New ?? 0}</dd>
            <dt>changed</dt><dd>{s.Changed ?? 0}</dd>
            <dt>unchanged</dt><dd>{s.Unchanged ?? 0}</dd>
          </dl>
        )}
      </Card>
    </div>
  );
}
