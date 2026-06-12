// StorageView — the customer storage surface: buy a managed drive, see your
// volumes, map one to your workstation. A dumb view: every action calls Go.

import { useCallback, useEffect, useState } from "react";
import { Button, Card } from "../../../../lib/design";
import { VolumeCard } from "../components/VolumeCard";
import { storageService } from "../storage.svc";
import { TIERS, type Tier, type Volume } from "../types";
import "./StorageView.css";

// Demo tenant; the real app derives this from the authenticated session.
const TENANT = "wardenclyffe";

export function StorageView() {
  const [volumes, setVolumes] = useState<Volume[]>([]);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const refresh = useCallback(async () => {
    try {
      const r = await storageService.list(TENANT);
      setVolumes(r.items);
      setError(null);
    } catch (e) {
      setError(e instanceof Error ? e.message : String(e));
    }
  }, []);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  const run = useCallback(
    async (fn: () => Promise<unknown>) => {
      setBusy(true);
      try {
        await fn();
        await refresh();
      } catch (e) {
        setError(e instanceof Error ? e.message : String(e));
      } finally {
        setBusy(false);
      }
    },
    [refresh],
  );

  const purchase = (tier: Tier) => run(() => storageService.purchase({ tenant_id: TENANT, tier }));
  const release = (id: string) => run(() => storageService.deprovision(id));
  const map = (id: string) =>
    run(async () => {
      const g = await storageService.grantMount(id, "s3");
      window.alert(
        `Map ${id}\nS3: ${g.s3_endpoint}\nbucket: ${g.bucket}\nkey: ${g.access_key}\nexpires: ${g.expires_at}`,
      );
    });

  return (
    <section className="storage-view">
      <header className="storage-head">
        <h1>Storage</h1>
        <p>Buy a managed drive and map it to your workstation.</p>
      </header>

      {error && (
        <p role="alert" className="storage-error">
          {error}
        </p>
      )}

      <div className="storage-tiers">
        {TIERS.map((t) => (
          <Card key={t.tier} title={t.label}>
            <p className="tier-blurb">{t.blurb}</p>
            <Button tone="brand" variant="solid" isDisabled={busy} onPress={() => purchase(t.tier)}>
              Purchase
            </Button>
          </Card>
        ))}
      </div>

      <h2 className="storage-sub">Your volumes</h2>
      <div className="storage-grid">
        {volumes.length === 0 ? (
          <p className="storage-empty">No volumes yet.</p>
        ) : (
          volumes.map((v) => (
            <VolumeCard key={v.id} volume={v} busy={busy} onMap={map} onRelease={release} />
          ))
        )}
      </div>
    </section>
  );
}
