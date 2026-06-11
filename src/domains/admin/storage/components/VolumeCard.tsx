// VolumeCard — a coldlight component built from the design directory (Card,
// Badge, Button over RAC). Colors come from the OKLCH tone engine; spacing/type
// from rem + clamp tokens. No bespoke color CSS.

import { Badge, Button, Card, type Tone } from "../../../../lib/design";
import type { Volume, VolumeState } from "../types";
import "./VolumeCard.css";

const STATE_TONE: Record<VolumeState, Tone> = {
  requested: "neutral",
  provisioning: "info",
  active: "success",
  suspended: "warning",
  deprovisioning: "warning",
  failed: "danger",
};

export interface VolumeCardProps {
  volume: Volume;
  busy?: boolean;
  onMap?: (id: string) => void;
  onRelease?: (id: string) => void;
}

export function VolumeCard({ volume, busy = false, onMap, onRelease }: VolumeCardProps) {
  const { spec } = volume;
  return (
    <Card title={volume.id} action={<Badge tone={STATE_TONE[volume.state]}>{volume.state}</Badge>}>
      <dl className="vol-facts">
        <div>
          <dt>Tier</dt>
          <dd>{spec.tier}</dd>
        </div>
        <div>
          <dt>Capacity</dt>
          <dd>{spec.capacity_gb} GB</dd>
        </div>
        <div>
          <dt>Replication</dt>
          <dd>
            {spec.replication} ×{spec.replication_factor}
          </dd>
        </div>
        <div>
          <dt>Bucket</dt>
          <dd>{volume.bucket ?? "—"}</dd>
        </div>
      </dl>
      {volume.mount_hint && <p className="vol-hint">{volume.mount_hint}</p>}
      <div className="vol-actions">
        <Button size="sm" tone="brand" variant="solid" isDisabled={busy} onPress={() => onMap?.(volume.id)}>
          Map to workstation
        </Button>
        <Button size="sm" tone="danger" variant="outline" isDisabled={busy} onPress={() => onRelease?.(volume.id)}>
          Release
        </Button>
      </div>
    </Card>
  );
}
