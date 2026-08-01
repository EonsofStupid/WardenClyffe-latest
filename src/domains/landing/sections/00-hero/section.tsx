import "./section.css";

export function Section() {
  return (
    <section id="hero" className="landing-section landing-hero">
      <h1>WardenClyffe</h1>
      <p>
        AI coding cloud for vibers — work from your machine, we run the remote box. Double‑click open;
        SSH and sysadmin stay behind the scenes.
      </p>
      <div style={{ display: "flex", flexWrap: "wrap", gap: "0.75rem", alignItems: "center" }}>
        <a className="landing-cta" href="/clyffe/code">
          Open Clyffe Code (mock)
        </a>
        <a className="landing-cta" href="/admin" style={{ opacity: 0.85 }}>
          Operator sign in
        </a>
      </div>
    </section>
  );
}
