// Admin · intelligence layer — touchpoint inventory health. Dumb view.
import { useEffect, useState } from "react";
import { Card } from "../../../../lib/design";
import { meshService } from "../../../warden/mesh/mesh.svc";
import type { IntelligenceInventory } from "../../../warden/mesh/types";

export function IntelligenceLayerView() {
  const [inv, setInv] = useState<IntelligenceInventory | null>(null);
  const [err, setErr] = useState<string | null>(null);

  useEffect(() => {
    meshService.intelligence().then(setInv).catch((e) => setErr((e as Error).message));
  }, []);

  return (
    <div>
      <h1 className="page-title">Intelligence layer</h1>
      <p className="page-sub">Touchpoint inventory — what routes, what projects, what drifts.</p>
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
    </div>
  );
}
