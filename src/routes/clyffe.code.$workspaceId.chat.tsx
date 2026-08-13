import { createFileRoute } from "@tanstack/react-router";
import { WorkspaceChatView } from "../domains/clyffe/code";

export const Route = createFileRoute("/clyffe/code/$workspaceId/chat")({
  component: WorkspaceChatView,
});
