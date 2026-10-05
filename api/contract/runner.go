package contract

import (
	"bytes"
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"io"
	"mime"
	"net/http"
	"sort"
	"strings"
	"time"
)

// Result is the outcome of replaying one fixture.
type Result struct {
	ID       string        `json:"id"`
	Route    string        `json:"route"`
	Status   string        `json:"status"` // pass | fail | skip | error
	Reason   string        `json:"reason,omitempty"`
	Diffs    []Diff        `json:"diffs,omitempty"`
	Duration time.Duration `json:"duration_ms"`
}

// Runner replays fixtures against BaseURL using DB for seeding.
type Runner struct {
	BaseURL string
	DB      *sql.DB
	Client  *http.Client
	Now     func() time.Time
	// Config holds the non-default config the server under test was
	// started with (key -> value, as in -config key=value). A fixture
	// recorded with other config overrides is skipped.
	Config     map[string]string
	normalizer *Normalizer
	seeder     *Seeder
}

// NewRunner prepares a runner.
func NewRunner(ctx context.Context, baseURL string, db *sql.DB) (*Runner, error) {
	n, err := NewNormalizer()
	if err != nil {
		return nil, err
	}
	s, err := NewSeeder(ctx, db)
	if err != nil {
		return nil, err
	}
	return &Runner{
		BaseURL:    baseURL,
		DB:         db,
		Client:     &http.Client{Timeout: 30 * time.Second, CheckRedirect: func(*http.Request, []*http.Request) error { return http.ErrUseLastResponse }},
		Now:        time.Now,
		normalizer: n,
		seeder:     s,
	}, nil
}

// Run replays one fixture.
func (r *Runner) Run(ctx context.Context, f *Fixture) Result {
	start := time.Now()
	res := Result{ID: f.ID(), Route: f.Route}
	defer func() { res.Duration = time.Since(start) / time.Millisecond }()

	if !f.Replayable {
		res.Status, res.Reason = "skip", f.NotReplayableReason
		return res
	}

	if need := r.configMismatch(f); need != "" {
		res.Status, res.Reason = "skip", "needs a server running with "+need
		return res
	}

	shift, err := NewTimeShift(f.RecordedAt, f.Timezone, r.Now())
	if err != nil {
		res.Status, res.Reason = "error", "time shift: "+err.Error()
		return res
	}
	bearer, err := r.seeder.Load(ctx, f, shift)
	if err != nil {
		res.Status, res.Reason = "error", err.Error()
		return res
	}
	req, err := BuildRequest(r.BaseURL, f, bearer, shift)
	if err != nil {
		res.Status, res.Reason = "error", "build request: "+err.Error()
		return res
	}
	resp, err := r.Client.Do(req.WithContext(ctx))
	if err != nil {
		res.Status, res.Reason = "error", "http: "+err.Error()
		return res
	}
	defer func() { _ = resp.Body.Close() }()
	body, _ := io.ReadAll(resp.Body)

	res.Diffs = r.check(f, resp, body)
	if len(res.Diffs) == 0 {
		res.Status = "pass"
	} else {
		res.Status = "fail"
	}
	return res
}

func (r *Runner) check(f *Fixture, resp *http.Response, body []byte) []Diff {
	var diffs []Diff
	if resp.StatusCode != f.Response.Status {
		diffs = append(diffs, Diff{"status", fmt.Sprint(f.Response.Status), fmt.Sprint(resp.StatusCode)})
	}
	ct, _, _ := mime.ParseMediaType(resp.Header.Get("Content-Type"))
	if f.Response.ContentType != "" && ct != f.Response.ContentType {
		diffs = append(diffs, Diff{"content-type", f.Response.ContentType, ct})
	}
	if len(f.Response.JSON) == 0 {
		return diffs // non-JSON bodies: status and content type only
	}

	expected, err := decode(f.Response.JSON)
	if err != nil {
		return append(diffs, Diff{"$", "valid fixture JSON", err.Error()})
	}
	actual, err := decode(body)
	if err != nil {
		snippet := string(body)
		if len(snippet) > 200 {
			snippet = snippet[:200] + "…"
		}
		return append(diffs, Diff{"$", "JSON body", "non-JSON: " + snippet})
	}
	return append(diffs, Compare(expected, r.normalizer.Normalize(actual, ""))...)
}

func decode(b []byte) (any, error) {
	dec := json.NewDecoder(bytes.NewReader(b))
	dec.UseNumber()
	var v any
	if err := dec.Decode(&v); err != nil {
		return nil, err
	}
	return v, nil
}

// configMismatch returns "" when the server under test runs with exactly
// the config overrides f was recorded with, otherwise a description of the
// config f needs.
func (r *Runner) configMismatch(f *Fixture) string {
	same := len(f.Config) == len(r.Config)
	need := make([]string, 0, len(f.Config))
	for k, v := range f.Config {
		want := ConfigValue(v)
		need = append(need, k+"="+want)
		if got, ok := r.Config[k]; !ok || got != want {
			same = false
		}
	}
	if same {
		return ""
	}
	if len(need) == 0 {
		return "the default config"
	}
	sort.Strings(need)
	return strings.Join(need, ", ")
}
