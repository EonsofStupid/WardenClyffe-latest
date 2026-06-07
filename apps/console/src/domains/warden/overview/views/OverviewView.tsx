import { useEffect, useState } from "react";
import { api, type ClyffyHome } from "../../../../lib/api";
import { PageHeader, Grid, Card, Badge, Stack } from "../../../../lib/design";
import "./OverviewView.css";

/** OverviewView — the post-login operator landing: a compact, optimized
 *  dashboard of the foundation at a glance. */
export function OverviewView() {
  const [workspaces, setWorkspaces] = useState<number | null>(null);
  const [home, setHome] = useState<ClyffyHome | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    api
      .workspaces()
      .then((r) => setWorkspaces(r.count))
      .catch((e) => setError(e instanceof Error ? e.message : String(e)));
    api.clyffyHome().then(setHome).catch(() => undefined);
  }, []);

  const owed = home?.summary.config_items_owed ?? null;

  return (
    <Stack gap={5}>
      <PageHeader title="Overview" subtitle="WardenClyffe operator dashboard" />
      {error && (
        <p className="ov-error" role="alert">
          {error}
        </p>
      )}
      <Grid gap={4} min={14}>
        <Card title="Workspaces" action={<Badge tone="neutral">fleet</Badge>}>
          <div className="ov-metric">{workspaces ?? "…"}</div>
        </Card>
        <Card title="Services" action={<Badge tone="info">mesh</Badge>}>
          <div className="ov-metric">{home?.summary.services ?? "…"}</div>
        </Card>
        <Card title="Platforms" action={<Badge tone="neutral">infra</Badge>}>
          <div className="ov-metric">{home?.summary.platforms ?? "…"}</div>
        </Card>
        <Card
          title="Config owed"
          action={<Badge tone={owed && owed > 0 ? "warning" : "success"}>setup</Badge>}
        >
          <div className="ov-metric">{owed ?? "…"}</div>
        </Card>
      </Grid>
    </Stack>
  );
}
