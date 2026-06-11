import { createFileRoute } from "@tanstack/react-router";
import { ClyffyView } from "../domains/clyffy";

export const Route = createFileRoute("/clyffy")({ component: ClyffyView });
