import type { ReactNode } from "react";
import { Card, Badge, type Tone } from "../../../../lib/design";
import "./MetricCard.css";

export interface MetricCardProps {
  label: string;
  value: ReactNode;
  tag?: string;
  tone?: Tone;
}

/** MetricCard — overview's colocated variant of the base Card: an embossed
 *  coldlight surface for a single headline metric. Composes lib/design; never
 *  re-implements the surface treatment. */
export function MetricCard({ label, value, tag, tone = "neutral" }: MetricCardProps) {
  return (
    <Card surface="embossed" title={label} action={tag ? <Badge tone={tone}>{tag}</Badge> : undefined}>
      <div className="metric-card__value">{value}</div>
    </Card>
  );
}
