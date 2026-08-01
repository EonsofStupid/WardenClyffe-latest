import { createFileRoute } from "@tanstack/react-router";
import { ClyffeCodeHomeView } from "../domains/clyffe/code";

export const Route = createFileRoute("/clyffe/code")({
  component: ClyffeCodeHomeView,
});
