// Package contract replays the recorded Laravel API fixtures against any
// server (Laravel today, the Go API later) and reports differences.
//
// A fixture (api/contract/fixtures/<METHOD>-<route>/<case>.json) holds the
// database state right before the request, who was signed in, the request
// and the normalised response. See api/contract/README.md.
package contract

import (
	"bytes"
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"sort"
	"strings"
)

// Fixture is one recorded request/response pair.
type Fixture struct {
	Name                string `json:"name"`
	Test                string `json:"test"`
	Route               string `json:"route"`
	Replayable          bool   `json:"replayable"`
	NotReplayableReason string `json:"not_replayable_reason,omitempty"`
	RecordedAt          string `json:"recorded_at"`
	Timezone            string `json:"timezone"`
	Auth                *Auth  `json:"auth"`
	// Config lists config keys the test changed from the defaults (for
	// example a feature flag). The fixture only replays against a server
	// started with the same values; see Runner.Config.
	Config   map[string]any `json:"config,omitempty"`
	Request  Request        `json:"request"`
	Response Response       `json:"response"`
	Seed     Seed           `json:"seed"`

	// Path is the file the fixture was loaded from (not part of the JSON).
	Path string `json:"-"`
}

// Auth describes the signed-in user, if any.
type Auth struct {
	Type   string `json:"type"`
	UserID int64  `json:"user_id"`
}

// Request is the recorded HTTP request.
type Request struct {
	Method  string            `json:"method"`
	Path    string            `json:"path"`
	Query   map[string]any    `json:"query"`
	Headers map[string]any    `json:"headers"`
	JSON    json.RawMessage   `json:"json,omitempty"`
	Raw     *string           `json:"raw,omitempty"`
	Form    map[string]any    `json:"form,omitempty"`
	Files   []File            `json:"files,omitempty"`
	Extra   map[string]string `json:"-"`
}

// File is an uploaded file (multipart).
type File struct {
	Field    string `json:"field"`
	Filename string `json:"filename"`
	Mime     string `json:"mime"`
	Base64   string `json:"base64"`
}

// Response is the recorded, normalised response.
type Response struct {
	Status      int             `json:"status"`
	ContentType string          `json:"content_type"`
	JSON        json.RawMessage `json:"json,omitempty"`
	BodySHA1    string          `json:"body_sha1,omitempty"`
}

// Seed is the database state before the request.
type Seed struct {
	Driver        string                      `json:"driver"`
	Tables        map[string][]map[string]any `json:"tables"`
	AutoIncrement map[string]int64            `json:"auto_increment"`
}

// LoadFixtures reads every *.json under dir, sorted by path.
func LoadFixtures(dir string) ([]*Fixture, error) {
	var paths []string
	err := filepath.WalkDir(dir, func(p string, d os.DirEntry, err error) error {
		if err != nil {
			return err
		}
		if !d.IsDir() && strings.HasSuffix(p, ".json") {
			paths = append(paths, p)
		}
		return nil
	})
	if err != nil {
		return nil, err
	}
	sort.Strings(paths)

	out := make([]*Fixture, 0, len(paths))
	for _, p := range paths {
		f, err := LoadFixture(p)
		if err != nil {
			return nil, err
		}
		out = append(out, f)
	}
	return out, nil
}

// LoadFixture reads one fixture file.
func LoadFixture(path string) (*Fixture, error) {
	b, err := os.ReadFile(path)
	if err != nil {
		return nil, err
	}
	dec := json.NewDecoder(bytes.NewReader(b))
	dec.UseNumber()
	var f Fixture
	if err := dec.Decode(&f); err != nil {
		return nil, fmt.Errorf("%s: %w", path, err)
	}
	f.Path = path
	return &f, nil
}

// ID is a short, stable identifier: "<dir>/<case>".
func (f *Fixture) ID() string {
	return filepath.Base(filepath.Dir(f.Path)) + "/" + strings.TrimSuffix(filepath.Base(f.Path), ".json")
}

// ConfigValue renders a recorded config value the way it is passed on the
// replay command line (-config key=value).
func ConfigValue(v any) string {
	switch t := v.(type) {
	case nil:
		return "null"
	case string:
		return t
	default:
		b, _ := json.Marshal(t)
		return string(b)
	}
}
