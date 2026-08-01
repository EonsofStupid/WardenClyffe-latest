import { createFileRoute } from "@tanstack/react-router";
import { OpenFlowView } from "../domains/clyffe/code";

export const Route = createFileRoute("/clyffe/code/$workspaceId/open")({
  component: OpenFlowView,
});
