package contract

import (
	"regexp"
	"time"
	_ "time/tzdata" // fixtures use Asia/Ashgabat; do not depend on the host's zoneinfo
)

// Datetime layouts that are shifted when replaying. Date-only values
// (birth dates) are left alone.
var shiftLayouts = []struct {
	re     *regexp.Regexp
	layout string
}{
	{regexp.MustCompile(`^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$`), "2006-01-02 15:04:05"},
	{regexp.MustCompile(`^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$`), "2006-01-02 15:04"},
	{regexp.MustCompile(`^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$`), "2006-01-02T15:04:05.000000Z"},
	{regexp.MustCompile(`^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(Z|[+-]\d{2}:\d{2})$`), time.RFC3339},
	{regexp.MustCompile(`^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$`), "2006-01-02T15:04:05"},
	{regexp.MustCompile(`^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$`), "2006-01-02T15:04"},
}

// TimeShift moves recorded datetimes forward by the time that passed since
// recording, so "an hour ago", "tomorrow at 15:00" and "after:now" keep
// their meaning when a fixture is replayed later.
type TimeShift struct {
	loc   *time.Location
	delta time.Duration
}

// NewTimeShift computes the shift for a fixture recorded at recordedAt
// (layout "2006-01-02 15:04:05" in timezone tz) relative to now.
func NewTimeShift(recordedAt, tz string, now time.Time) (*TimeShift, error) {
	loc, err := time.LoadLocation(tz)
	if err != nil {
		return nil, err
	}
	t0, err := time.ParseInLocation("2006-01-02 15:04:05", recordedAt, loc)
	if err != nil {
		return nil, err
	}
	return &TimeShift{loc: loc, delta: now.Sub(t0).Truncate(time.Second)}, nil
}

// String shifts s if it is a datetime in one of the known layouts.
func (ts *TimeShift) String(s string) string {
	if ts == nil || ts.delta == 0 {
		return s
	}
	for _, l := range shiftLayouts {
		if !l.re.MatchString(s) {
			continue
		}
		loc := ts.loc
		if l.layout == "2006-01-02T15:04:05.000000Z" {
			loc = time.UTC // the Z is literal in the layout
		}
		t, err := time.ParseInLocation(l.layout, s, loc)
		if err != nil {
			return s
		}
		return t.Add(ts.delta).Format(l.layout)
	}
	return s
}

// Value shifts every datetime string inside v (maps, slices, strings).
func (ts *TimeShift) Value(v any) any {
	switch t := v.(type) {
	case map[string]any:
		out := make(map[string]any, len(t))
		for k, val := range t {
			out[k] = ts.Value(val)
		}
		return out
	case []any:
		out := make([]any, len(t))
		for i, val := range t {
			out[i] = ts.Value(val)
		}
		return out
	case string:
		return ts.String(t)
	default:
		return v
	}
}
