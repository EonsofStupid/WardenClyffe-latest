import "./Surface.css";
import "./Card.css";
import type { SurfaceVariant } from "./Surface";

export interface CardProps {
  title?: React.ReactNode;
  action?: React.ReactNode;
  children: React.ReactNode;
  /** coldlight surface treatment: flat | raised | embossed | glass | sunken */
  surface?: SurfaceVariant;
  /** @deprecated use `surface`. subtle -> flat, raised -> raised */
  elevation?: "subtle" | "raised";
}

/** Card — titled coldlight surface container. The surface treatment is shared
 *  with Surface via [data-surface]; domains pick the variant. */
export function Card({ title, action, children, surface, elevation }: CardProps) {
  const variant: SurfaceVariant = surface ?? (elevation === "subtle" ? "flat" : "raised");
  return (
    <section className="ui-card" data-surface={variant}>
      {(title || action) && (
        <header className="ui-card__header">
          <h3>{title}</h3>
          {action}
        </header>
      )}
      <div className="ui-card__body">{children}</div>
    </section>
  );
}
