import { useEffect } from "react";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { LoginView, useAuth } from "../domains/warden/identity";

// /login — the unauthenticated entry. Once an operator is present (fresh login
// or a still-valid token) we send them on to /admin, the authenticated shell.
function LoginRoute() {
  const { operator } = useAuth();
  const navigate = useNavigate();
  useEffect(() => {
    if (operator) void navigate({ to: "/admin" });
  }, [operator, navigate]);
  return <LoginView />;
}

export const Route = createFileRoute("/login")({ component: LoginRoute });
