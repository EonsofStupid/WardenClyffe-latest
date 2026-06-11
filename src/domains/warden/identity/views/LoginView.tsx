import { type FormEvent, useState } from "react";
import { TextField, Label, Input } from "react-aria-components";
import { Button } from "../../../../lib/design";
import { useAuth } from "../useAuth";
import "./LoginView.css";

/** LoginView — warden.rrflow.ai sign-in. The fields blend into an animated,
 *  seamless looping coldlight aurora (GPU-only transforms; reduced-motion safe). */
export function LoginView() {
  const { login } = useAuth();
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  const onSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    setError(null);
    try {
      await login(username, password);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Login failed");
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="login">
      <div className="login__aurora" aria-hidden="true">
        <span className="login__blob login__blob--a" />
        <span className="login__blob login__blob--b" />
        <span className="login__blob login__blob--c" />
      </div>

      <form className="login__card" onSubmit={onSubmit}>
        <div className="login__brand">
          WardenClyffe
          <small>warden operator</small>
        </div>

        <TextField className="login__field" value={username} onChange={setUsername} autoFocus>
          <Label>Username</Label>
          <Input name="username" placeholder="operator" autoComplete="username" />
        </TextField>

        <TextField className="login__field" value={password} onChange={setPassword} type="password">
          <Label>Password</Label>
          <Input name="password" placeholder="••••••••" autoComplete="current-password" />
        </TextField>

        {error && (
          <p className="login__error" role="alert">
            {error}
          </p>
        )}

        <Button type="submit" tone="brand" variant="solid" isDisabled={busy}>
          {busy ? "Signing in…" : "Sign in"}
        </Button>
      </form>
    </div>
  );
}
