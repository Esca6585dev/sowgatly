package contract

import (
	"encoding/json"
	"fmt"
	"math/big"
	"sort"
	"strings"
)

// Diff is one difference between the expected and the actual response.
type Diff struct {
	Path     string `json:"path"`
	Expected string `json:"expected"`
	Actual   string `json:"actual"`
}

func (d Diff) String() string {
	return fmt.Sprintf("%s: expected %s, got %s", d.Path, d.Expected, d.Actual)
}

// Compare walks expected and actual (both already normalised) and returns
// the differences: missing or extra object keys, array lengths, JSON types
// and values. Numbers are compared by value (1 == 1.0); object key order is
// ignored; array order matters.
func Compare(expected, actual any) []Diff {
	var diffs []Diff
	compareAt("$", expected, actual, &diffs)
	return diffs
}

func compareAt(path string, exp, act any, diffs *[]Diff) {
	et, at := jsonType(exp), jsonType(act)
	if et != at {
		*diffs = append(*diffs, Diff{path, describe(exp), describe(act)})
		return
	}
	switch e := exp.(type) {
	case map[string]any:
		a := act.(map[string]any)
		keys := make([]string, 0, len(e)+len(a))
		seen := map[string]bool{}
		for k := range e {
			keys = append(keys, k)
			seen[k] = true
		}
		for k := range a {
			if !seen[k] {
				keys = append(keys, k)
			}
		}
		sort.Strings(keys)
		for _, k := range keys {
			ev, eok := e[k]
			av, aok := a[k]
			p := path + "." + k
			switch {
			case !aok:
				*diffs = append(*diffs, Diff{p, describe(ev), "(missing)"})
			case !eok:
				*diffs = append(*diffs, Diff{p, "(absent)", describe(av)})
			default:
				compareAt(p, ev, av, diffs)
			}
		}
	case []any:
		a := act.([]any)
		if len(e) != len(a) {
			*diffs = append(*diffs, Diff{path, fmt.Sprintf("array of %d", len(e)), fmt.Sprintf("array of %d", len(a))})
		}
		for i := 0; i < len(e) && i < len(a); i++ {
			compareAt(fmt.Sprintf("%s[%d]", path, i), e[i], a[i], diffs)
		}
	case json.Number:
		if !numbersEqual(e, act.(json.Number)) {
			*diffs = append(*diffs, Diff{path, describe(exp), describe(act)})
		}
	default:
		if exp != act {
			*diffs = append(*diffs, Diff{path, describe(exp), describe(act)})
		}
	}
}

func numbersEqual(a, b json.Number) bool {
	if a == b {
		return true
	}
	x, ok1 := new(big.Float).SetString(a.String())
	y, ok2 := new(big.Float).SetString(b.String())
	return ok1 && ok2 && x.Cmp(y) == 0
}

func jsonType(v any) string {
	switch v.(type) {
	case nil:
		return "null"
	case bool:
		return "boolean"
	case json.Number, float64, int, int64:
		return "number"
	case string:
		return "string"
	case []any:
		return "array"
	case map[string]any:
		return "object"
	default:
		return fmt.Sprintf("%T", v)
	}
}

func describe(v any) string {
	switch t := v.(type) {
	case nil:
		return "null"
	case string:
		s := t
		if len(s) > 80 {
			s = s[:77] + "..."
		}
		return fmt.Sprintf("string %q", s)
	case json.Number:
		return "number " + t.String()
	case bool:
		return fmt.Sprintf("boolean %v", t)
	case []any:
		return fmt.Sprintf("array of %d", len(t))
	case map[string]any:
		keys := make([]string, 0, len(t))
		for k := range t {
			keys = append(keys, k)
		}
		sort.Strings(keys)
		s := strings.Join(keys, ",")
		if len(s) > 80 {
			s = s[:77] + "..."
		}
		return "object {" + s + "}"
	default:
		return fmt.Sprintf("%v", t)
	}
}
