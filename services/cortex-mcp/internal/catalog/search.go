package catalog

import (
	"bufio"
	"os"
	"path/filepath"
	"strings"
)

// DecisionMatch is one search hit inside a decision touchpoint.
type DecisionMatch struct {
	ProjectKey string `json:"project_key"`
	Path       string `json:"path"`
	Line       int    `json:"line"`
	Text       string `json:"text"`
}

// searchFiles case-insensitively scans the decision touchpoints for query.
func searchFiles(dir, query string) ([]DecisionMatch, error) {
	q := strings.ToLower(query)
	matches := []DecisionMatch{}

	entries, err := os.ReadDir(dir)
	if err != nil {
		return nil, err
	}
	for _, e := range entries {
		if e.IsDir() || !strings.HasSuffix(e.Name(), ".md") {
			continue
		}
		path := filepath.Join(dir, e.Name())
		f, err := os.Open(path)
		if err != nil {
			continue
		}
		sc := bufio.NewScanner(f)
		sc.Buffer(make([]byte, 0, 1<<16), 1<<22)
		n := 0
		for sc.Scan() {
			n++
			line := sc.Text()
			if strings.Contains(strings.ToLower(line), q) {
				text := strings.TrimSpace(line)
				if len(text) > 240 {
					text = text[:240] + "…"
				}
				matches = append(matches, DecisionMatch{
					ProjectKey: strings.TrimSuffix(e.Name(), ".md"),
					Path:       path,
					Line:       n,
					Text:       text,
				})
			}
		}
		f.Close()
	}
	return matches, nil
}
