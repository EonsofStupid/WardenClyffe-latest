// Clyffy — the assistant surface (clyffy.ai). Persona/orchestrator only; never
// product authority (Warden) or customer plane (Clyffe). Reads via /api/clyffy/*.
export function ClyffyView() {
  return (
    <section className="clyffy">
      <h1>Clyffy</h1>
      <p>Assistant + orchestrator surface. Scaffold — wire to /api/clyffy/* next.</p>
    </section>
  );
}
