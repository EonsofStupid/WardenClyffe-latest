package httpapi

import (
	"context"
	"errors"
	"net/http"
	"time"

	"shippin.cloud/vaultix/panel/internal/core"
	"shippin.cloud/vaultix/panel/internal/source"
)

// sourceOps adapts the Server to source.Ops: the source-API driver performs
// the same operations the panel's own handlers do, over the local core.
type sourceOps struct{ s *Server }

func (o sourceOps) CreateProject(ctx context.Context, name string) (string, error) {
	c, err := o.s.localClient(ctx)
	if err != nil {
		return "", err
	}
	return c.CreateProject(ctx, name)
}

func (o sourceOps) PutSecret(ctx context.Context, projectID, environment, name, value string) error {
	c, err := o.s.localClient(ctx)
	if err != nil {
		return err
	}
	return c.UpsertSecret(ctx, projectID, environment, core.Secret{Key: name, Value: value})
}

func (o sourceOps) Inject(ctx context.Context, user, projectID, environment, name string) (string, time.Time, error) {
	handle, exp := o.s.mintInjectHandle(user, projectID, environment, name)
	return handle, exp, nil
}

// sourceManifest serves the action catalog — the same surface the UI renders
// and a Vaultix specialist Clyffy discovers. Labels + narration only; no
// secrets. Available to any authenticated identity.
func (s *Server) sourceManifest(w http.ResponseWriter, _ *http.Request, _ string) {
	writeJSON(w, http.StatusOK, map[string]any{"boundary": "vaultix", "actions": source.Manifest()})
}

// sourceAct executes one action and returns the observable, narrated Result
// the viewport renders. Identical path for a human operator and a Clyffy —
// the actor is recorded, never a hidden route. Elevated (PIN step-up).
func (s *Server) sourceAct(w http.ResponseWriter, r *http.Request, u string) {
	var in struct {
		ActionID string            `json:"actionId"`
		Actor    string            `json:"actor"`
		Inputs   map[string]string `json:"inputs"`
	}
	if !readJSON(w, r, &in) {
		return
	}
	actor := source.ActorOperator
	if in.Actor == string(source.ActorClyffy) {
		actor = source.ActorClyffy
	}
	driver := source.Driver{Ops: sourceOps{s}}
	res, err := driver.Execute(r.Context(), in.ActionID, actor, u, in.Inputs)

	// Audit every action with the actor — a Clyffy action is as visible in
	// the log as it is in the viewport.
	target := in.ActionID
	if pid, ok := res.Meta["projectId"].(string); ok {
		target += " " + pid
	}
	s.Audit.Record(u, "source.act."+string(actor), target, err == nil, errText(err))

	switch {
	case errors.Is(err, source.ErrUnknownAction):
		fail(w, http.StatusNotFound, "unknown action")
	case errors.Is(err, source.ErrMissingInput):
		fail(w, http.StatusBadRequest, err.Error())
	case err != nil:
		// Still return the observable Result (with its failed step) so the
		// viewport shows what happened, plus the gateway error.
		writeJSON(w, http.StatusBadGateway, map[string]any{"error": err.Error(), "result": res})
	default:
		writeJSON(w, http.StatusOK, map[string]any{"result": res})
	}
}
