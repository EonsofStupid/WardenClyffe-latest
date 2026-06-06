// Tiny className joiner (no dependency). Falsy values are dropped.
export function cx(...parts: Array<string | false | null | undefined>): string {
  return parts.filter(Boolean).join(" ");
}

// Shared variant prop types for the ui components.
export type Tone = "brand" | "neutral" | "success" | "warning" | "danger" | "info";
export type Size = "sm" | "md" | "lg";
