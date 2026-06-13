// Warden · identity — route guard. Redirects anonymous visitors to /login and
// renders its children only for an authenticated operator. Boring on purpose:
// it does exactly what its name says and nothing else.
import { useEffect, type ReactNode } from "react";
import { useNavigate } from "@tanstack/react-router";
import { useAuth } from "./useAuth";

export function RequireOperator({ children }: { children: ReactNode }) {
  const { operator, loading } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (!loading && !operator) void navigate({ to: "/login" });
  }, [loading, operator, navigate]);

  if (loading) return <div className="auth-pending">Authenticating…</div>;
  if (!operator) return null; // redirect in flight
  return <>{children}</>;
}
