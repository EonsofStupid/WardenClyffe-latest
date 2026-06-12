import { createFileRoute } from "@tanstack/react-router";
import { AdminView } from "../domains/admin";

export const Route = createFileRoute("/admin")({ component: AdminView });
