// Admin · control layer — every registered MCP plugin + copyable connect
// snippets. Dumb view: all data from meshService (Go drives).
import { useEffect, useState } from "react";
import { Badge, Button, Card } from "../../../../lib/design";
import { meshService } from "../../../warden/mesh/mesh.svc";
import type { ConnectDescriptors, Plugin } from "../../../warden/mesh/types";

export function ControlLayerView() {
  const [plugins, setPlugins] = useState<Plugin[]>([]);
  const [connect, setConnect] = useState<ConnectDescriptors | null>(null);
  const [err, setErr] = useState<string | null>(null);

  useEffect(() => {
    meshService.plugins().then((r) => setPlugins(r.items)).catch((e) => setErr((e as Error).message));
  }, []);

  const show = (id: string) =>
    meshService.connect(id).then(setConnect).catch((e) => setErr((e as Error).message));

  return (
    <div>
      <h1 className="page-title">Control layer</h1>
      <p className="page-sub">MCP plugins from the Context Mesh registry — status and per-tool connect snippets.</p>
      {err && <Card title="Error"><span className="muted">{err}</span></Card>}
      <div className="grid-cards">
        {plugins.map((p) => (
          <Card key={p.id} title={p.slug ?? p.id} action={<Badge state={p.status === "formal-mcp" ? "running" : "requested"} />}>
            <dl className="kv">
              <dt>id</dt><dd className="mono">{p.id}</dd>
              <dt>class</dt><dd>{p.class ?? "—"}</dd>
              <dt>status</dt><dd>{p.status ?? "—"}</dd>
            </dl>
            <Button size="sm" tone="brand" variant="ghost" onPress={() => void show(p.id)}>Connect</Button>
          </Card>
        ))}
      </div>
      {connect && (
        <Card title={`Connect ${connect.id}`}>
          <dl className="kv">
            <dt>Claude Code</dt><dd className="mono">{connect.claude_code}</dd>
            <dt>Codex</dt><dd className="mono">{connect.codex_toml}</dd>
            <dt>Claude Desktop</dt><dd className="mono">{JSON.stringify(connect.claude_desktop)}</dd>
          </dl>
        </Card>
      )}
    </div>
  );
}
