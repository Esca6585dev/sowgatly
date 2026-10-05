package contract

import (
	_ "embed"
	"encoding/json"
	"fmt"
	"net/url"
	"regexp"
	"strings"
)

//go:embed normalize.json
var normalizeRules []byte

// Normalizer replaces volatile values with placeholders. The rules come from
// normalize.json, which the PHP recorder reads too.
type Normalizer struct {
	keys          map[string]string
	patterns      []pattern
	urlRe         *regexp.Regexp
	pathRe        *regexp.Regexp
	randomSegment *regexp.Regexp
}

type pattern struct {
	re *regexp.Regexp
	ph string
}

// NewNormalizer compiles the embedded rules.
func NewNormalizer() (*Normalizer, error) {
	var raw struct {
		Keys     map[string]string `json:"keys"`
		Patterns []struct {
			Regex       string `json:"regex"`
			Placeholder string `json:"placeholder"`
		} `json:"patterns"`
		URL struct {
			Regex         string `json:"regex"`
			PathRegex     string `json:"path_regex"`
			RandomSegment string `json:"random_segment"`
		} `json:"url"`
	}
	if err := json.Unmarshal(normalizeRules, &raw); err != nil {
		return nil, fmt.Errorf("normalize.json: %w", err)
	}
	n := &Normalizer{keys: raw.Keys}
	for _, p := range raw.Patterns {
		re, err := regexp.Compile(p.Regex)
		if err != nil {
			return nil, fmt.Errorf("pattern %q: %w", p.Regex, err)
		}
		n.patterns = append(n.patterns, pattern{re: re, ph: p.Placeholder})
	}
	var err error
	if n.urlRe, err = regexp.Compile(raw.URL.Regex); err != nil {
		return nil, err
	}
	if n.pathRe, err = regexp.Compile(raw.URL.PathRegex); err != nil {
		return nil, err
	}
	if n.randomSegment, err = regexp.Compile(raw.URL.RandomSegment); err != nil {
		return nil, err
	}
	return n, nil
}

// Normalize returns a copy of v with volatile values replaced. key is the
// object key v was found under ("" for array items and the root).
func (n *Normalizer) Normalize(v any, key string) any {
	if ph, ok := n.keys[key]; ok && key != "" && v != nil {
		return ph
	}
	switch t := v.(type) {
	case map[string]any:
		out := make(map[string]any, len(t))
		for k, val := range t {
			out[k] = n.Normalize(val, k)
		}
		return out
	case []any:
		out := make([]any, len(t))
		for i, val := range t {
			out[i] = n.Normalize(val, "")
		}
		return out
	case string:
		return n.normalizeString(t)
	default:
		return v
	}
}

func (n *Normalizer) normalizeString(s string) string {
	for _, p := range n.patterns {
		if p.re.MatchString(s) {
			return p.ph
		}
	}
	if n.urlRe.MatchString(s) {
		u, err := url.Parse(s)
		if err != nil {
			return "<url>"
		}
		out := "<url>" + n.normalizePath(u.EscapedPath())
		if u.RawQuery != "" {
			out += "?" + u.RawQuery
		}
		return out
	}
	if n.pathRe.MatchString(s) {
		return n.normalizePath(s)
	}
	return s
}

// normalizePath replaces random file-name segments of a URL path with <file>.
func (n *Normalizer) normalizePath(path string) string {
	segs := strings.Split(path, "/")
	for i, seg := range segs {
		if dec, err := url.PathUnescape(seg); err == nil {
			seg = dec
		}
		if n.randomSegment.MatchString(seg) {
			segs[i] = "<file>"
		} else {
			segs[i] = seg
		}
	}
	return strings.Join(segs, "/")
}
