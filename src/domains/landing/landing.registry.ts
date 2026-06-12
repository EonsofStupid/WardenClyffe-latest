// Dynamic landing-section registry.
//
// Convention (boring + predictable): every landing section is a folder
//   src/domains/landing/sections/<NN>-<slug>/
// containing:
//   section.meta.ts   -> exports `meta: LandingSectionMeta`
//   section.tsx        -> exports `Section` (the React component)
//   section.css        -> optional, ColdLight-token styles
//
// Drop a conventionally-named folder and it loads automatically — no manual
// route or import wiring. <NN> is a 2-digit order prefix (00, 10, 20, …).
import type { ComponentType } from "react";

export interface LandingSectionMeta {
  slug: string;
  navLabel: string;
  order: number;
  enabled: boolean;
}

export interface LandingSection extends LandingSectionMeta {
  Component: ComponentType;
}

const metas = import.meta.glob<{ meta: LandingSectionMeta }>(
  "./sections/*/section.meta.ts",
  { eager: true },
);
const comps = import.meta.glob<{ Section: ComponentType }>(
  "./sections/*/section.tsx",
  { eager: true },
);

const dirOf = (p: string) => p.replace(/\/section\.(meta\.ts|tsx)$/, "");

export const landingSections: LandingSection[] = Object.entries(metas)
  .map(([path, mod]) => {
    const comp = comps[`${dirOf(path)}/section.tsx`];
    return comp ? { ...mod.meta, Component: comp.Section } : null;
  })
  .filter((s): s is LandingSection => s !== null && s.enabled)
  .sort((a, b) => a.order - b.order);
