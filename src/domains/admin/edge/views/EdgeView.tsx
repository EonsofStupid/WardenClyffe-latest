// Admin · edge — public-IP inventory: add, update status, see assignments.
// Dumb view: all data + mutations go through edgeService (Go drives).
import { useEffect, useState, type FormEvent } from "react";
import { Badge, Button, Card } from "../../../../lib/design";
import { edgeService } from "../edge.svc";
import type { PublicIP, PublicIPRole } from "../types";

const ROLES: PublicIPRole[] = ["ingress", "egress", "exit", "reserved"];

export function EdgeView() {
  const [ips, setIPs] = useState<PublicIP[]>([]);
  const [err, setErr] = useState<string | null>(null);
  const [address, setAddress] = useState("");
  const [role, setRole] = useState<PublicIPRole>("ingress");
  const [label, setLabel] = useState("");
  const [busy, setBusy] = useState(false);

  const load = () =>
    edgeService.listIPs().then((r) => setIPs(r.items)).catch((e) => setErr((e as Error).message));

  useEffect(() => {
    void load();
  }, []);

  const add = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    setErr(null);
    try {
      await edgeService.createIP({ address, role, label: label || undefined });
      setAddress("");
      setLabel("");
      await load();
    } catch (e) {
      setErr((e as Error).message);
    } finally {
      setBusy(false);
    }
  };

  const setStatus = async (ip: PublicIP, status: "active" | "released") => {
    setErr(null);
    try {
      await edgeService.updateIP(ip.id, { status });
      await load();
    } catch (e) {
      setErr((e as Error).message);
    }
  };

  return (
    <div>
      <h1 className="page-title">Public IPs</h1>
      <p className="page-sub">The addresses WardenClyffe owns/routes — add, assign a role, and update status.</p>
      {err && <Card title="Error"><span className="muted">{err}</span></Card>}

      <Card title="Add a public IP">
        <form className="edge-add" onSubmit={add}>
          <input aria-label="address" placeholder="203.0.113.10" value={address} onChange={(e) => setAddress(e.target.value)} required />
          <select aria-label="role" value={role} onChange={(e) => setRole(e.target.value as PublicIPRole)}>
            {ROLES.map((r) => <option key={r} value={r}>{r}</option>)}
          </select>
          <input aria-label="label" placeholder="label (optional)" value={label} onChange={(e) => setLabel(e.target.value)} />
          <Button type="submit" tone="brand" variant="solid" isDisabled={busy || !address}>
            {busy ? "Adding…" : "Add IP"}
          </Button>
        </form>
      </Card>

      <div className="grid-cards">
        {ips.map((ip) => (
          <Card
            key={ip.id}
            title={ip.address}
            action={<Badge state={ip.status === "active" ? "running" : "requested"} />}
          >
            <dl className="kv">
              <dt>role</dt><dd>{ip.role}</dd>
              <dt>status</dt><dd>{ip.status}</dd>
              <dt>label</dt><dd>{ip.label ?? "—"}</dd>
              <dt>provider</dt><dd>{ip.provider ?? "—"}</dd>
            </dl>
            {ip.status !== "active" && (
              <Button size="sm" tone="brand" variant="ghost" onPress={() => void setStatus(ip, "active")}>Activate</Button>
            )}
            {ip.status === "active" && (
              <Button size="sm" variant="ghost" onPress={() => void setStatus(ip, "released")}>Release</Button>
            )}
          </Card>
        ))}
      </div>
    </div>
  );
}
