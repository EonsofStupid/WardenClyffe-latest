export function Section() {
  const items = [
    ["Capsule or full VM", "Chat-only capsule, or a real VM with your own editor."],
    ["Bring your tools", "Claude Code, Codex, Cursor, Gemini — over Remote-SSH."],
    ["Persistent auth", "Brokered, refreshed, never leaked."],
  ];
  return (
    <section id="features" className="landing-section">
      <h2>What you get</h2>
      <ul className="landing-features">
        {items.map(([title, body]) => (
          <li key={title}>
            <strong>{title}</strong>
            <span>{body}</span>
          </li>
        ))}
      </ul>
    </section>
  );
}
