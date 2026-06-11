import { createFileRoute } from "@tanstack/react-router";
import { OverviewView } from "../domains/warden/overview";

export const Route = createFileRoute("/warden")({ component: OverviewView });
