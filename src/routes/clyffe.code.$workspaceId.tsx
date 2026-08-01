import { createFileRoute } from "@tanstack/react-router";
import { WorkspaceDetailView } from "../domains/clyffe/code";

export const Route = createFileRoute("/clyffe/code/$workspaceId")({
  component: WorkspaceDetailView,
});
