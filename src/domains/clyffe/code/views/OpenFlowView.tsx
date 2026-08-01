import { useEffect, useState } from "react";
import { Link, useParams } from "@tanstack/react-router";
import { Button } from "../../../../lib/design";
import { clyffeCodeService } from "../code.svc";
import { OPEN_STEPS } from "../mock-data";
import type { CustomerWorkspace } from "../types";
import { ClyffeCodeShell } from "./ClyffeCodeShell";

/** Mock “double-click open” theater — customer copy vs behind-the-scenes. */
export function OpenFlowView() {
  const { workspaceId } = useParams({ from: "/clyffe/code/$workspaceId/open" });
  const [ws, setWs] = useState<CustomerWorkspace | null>(null);
  const [stepIdx, setStepIdx] = useState(0);
  const [lines, setLines] = useState<string[]>([]);
  const [done, setDone] = useState(false);

  useEffect(() => {
    let cancelled = false;
    void (async () => {
      const { workspace } = await clyffeCodeService.open(workspaceId);
      if (cancelled) return;
      setWs(workspace);
      setLines([
        `$ clyffe open ${workspace.slug}`,
        `# you never type: ssh ${workspace.connectTarget}`,
      ]);

      for (let i = 0; i < OPEN_STEPS.length; i++) {
        if (cancelled) return;
        await new Promise((r) => setTimeout(r, 700));
        setStepIdx(i);
        const s = OPEN_STEPS[i];
        setLines((prev) => [
          ...prev,
          ``,
          `[${s.id}] ${s.label}`,
          `  you see:  ${s.customerSees}`,
          `  we do:    ${s.behindScenes}`,
        ]);
      }
      if (!cancelled) {
        setDone(true);
        setLines((prev) => [
          ...prev,
          ``,
          `ready · ${workspace.name}`,
          `agents: ${workspace.agents.join(", ")}`,
          `cwd: ~/workspace  ·  type claude | codex | grok`,
        ]);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [workspaceId]);

  return (
    <ClyffeCodeShell>
      <div className="cc-open">
        <div>
          <Link
            to="/clyffe/code/$workspaceId"
            params={{ workspaceId }}
            style={{ fontSize: "var(--wc-fs-sm)", color: "var(--wc-text-dim)" }}
          >
            ← {ws?.name ?? "Workspace"}
          </Link>
          <h1 style={{ margin: "0.5rem 0 0.25rem", fontSize: "var(--wc-fs-xl)" }}>
            Opening workspace
          </h1>
          <p className="muted" style={{ margin: 0 }}>
            This is the double‑click experience. Left: what vibers see. Right / terminal: truth.
          </p>
        </div>

        <div className="cc-open-terminal" aria-live="polite">
          {lines.map((line, i) => (
            <div key={i}>
              {line.startsWith("$") ? (
                <span className="prompt">{line}</span>
              ) : line.startsWith("  you see:") ? (
                <span className="hi">{line}</span>
              ) : line.startsWith("  we do:") || line.startsWith("#") ? (
                <span className="dim">{line}</span>
              ) : (
                line
              )}
            </div>
          ))}
          {!done && <div className="dim">▌</div>}
        </div>

        <ol className="cc-steps">
          {OPEN_STEPS.map((s, i) => (
            <li
              key={s.id}
              className="cc-step"
              data-active={i === stepIdx && !done ? "true" : undefined}
              data-done={i < stepIdx || done ? "true" : undefined}
            >
              <div className="cc-step-title">
                {i < stepIdx || done ? "✓ " : i === stepIdx ? "→ " : ""}
                {s.label}
              </div>
              <div className="cc-step-split">
                <span>
                  You: <em>{s.customerSees}</em>
                </span>
                <span>Behind: {s.behindScenes}</span>
              </div>
            </li>
          ))}
        </ol>

        {done && (
          <div className="cc-hero-actions">
            <Button tone="brand" variant="solid" onClick={() => window.location.reload()}>
              Replay open
            </Button>
            <Link to="/clyffe/code">
              <Button tone="neutral" variant="outline">
                Back to workspaces
              </Button>
            </Link>
          </div>
        )}

        <p className="cc-banner">
          Production: custom terminal / Clyffe Connect app performs these steps for real. This page is
          for product redline only.
        </p>
      </div>
    </ClyffeCodeShell>
  );
}
