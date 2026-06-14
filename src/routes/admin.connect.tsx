import { createFileRoute } from "@tanstack/react-router";
import { RequireOperator } from "../domains/warden/identity";
import { ConnectView } from "../domains/admin/connect";

export const Route = createFileRoute("/admin/connect")({
  component: () => (
    <RequireOperator>
      <ConnectView />
    </RequireOperator>
  ),
});
