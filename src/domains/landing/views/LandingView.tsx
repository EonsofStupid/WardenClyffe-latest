// Landing surface — renders every enabled section, in order, with nav built
// from the same registry. Sections are discovered dynamically; this view never
// imports them by hand.
import { landingSections } from "../landing.registry";

export function LandingView() {
  return (
    <div className="landing">
      <nav className="landing-nav">
        {landingSections.map((s) => (
          <a key={s.slug} href={`#${s.slug}`}>
            {s.navLabel}
          </a>
        ))}
      </nav>
      <main>
        {landingSections.map((s) => (
          <s.Component key={s.slug} />
        ))}
      </main>
    </div>
  );
}
