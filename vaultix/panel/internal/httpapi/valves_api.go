package httpapi

import "net/http"

// getCapabilities lists every dormant valve and whether it's open (doc 0003).
// Makes the option discoverable to the panel and to Clyffy even while closed.
func (s *Server) getCapabilities(w http.ResponseWriter, _ *http.Request, _ string) {
	m := s.Valves.Manifest()
	// Reflect the rate-cap pointer's live enabled flag into the manifest.
	if s.RateCap != nil {
		for i := range m {
			if m[i].Name == "rate-cap" {
				m[i].Enabled = s.RateCap.Enabled
			}
		}
	}
	writeJSON(w, http.StatusOK, map[string]any{"valves": m})
}
