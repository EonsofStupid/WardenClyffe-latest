import { createFileRoute } from "@tanstack/react-router";
import { OrderWorkspaceView } from "../domains/clyffe/code";

export const Route = createFileRoute("/clyffe/code/order")({
  component: OrderWorkspaceView,
});
