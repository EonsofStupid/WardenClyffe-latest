import "./Card.css";

export interface CardProps {
  title?: React.ReactNode;
  action?: React.ReactNode;
  children: React.ReactNode;
  /** subtle | raised — surface emphasis */
  elevation?: "subtle" | "raised";
}

/** Card — titled surface container. */
export function Card({ title, action, children, elevation = "raised" }: CardProps) {
  return (
    <section className="ui-card" data-elevation={elevation}>
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
