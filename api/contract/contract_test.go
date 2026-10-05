package contract

import (
	"encoding/json"
	"net/url"
	"strings"
	"testing"
	"time"
)

func mustJSON(t *testing.T, s string) any {
	t.Helper()
	v, err := decodeJSON([]byte(s))
	if err != nil {
		t.Fatalf("decode %s: %v", s, err)
	}
	return v
}

func TestNormalize(t *testing.T) {
	n, err := NewNormalizer()
	if err != nil {
		t.Fatal(err)
	}
	cases := []struct{ key, in, want string }{
		{"", "2026-10-05 12:30:00", "<datetime>"},
		{"", "2026-10-05T07:30:00.000000Z", "<datetime>"},
		{"", "2026-10-05T12:30:00+05:00", "<datetime>"},
		{"", "2026-10-05", "2026-10-05"},
		{"", "12|abcdefghijABCDEFGHIJabcdefghijABCDEFGHIJ", "<token>"},
		{"token", "anything", "<token>"},
		{"device_token", "YTN5SUPuHrDtshenuYmOsBpueboGo2Mx", "<token>"},
		{"", "http://127.0.0.1:8000/storage/shops/ab12CD34ef56GH78ij90.jpg", "<url>/storage/shops/<file>"},
		{"", "https://sowgatly.com/storage/logo.png?v=2", "<url>/storage/logo.png?v=2"},
		{"", "/storage/product_images/6ac34a8ddd121.png", "/storage/product_images/<file>"},
		{"", "/storage/brands/nike.png", "/storage/brands/nike.png"},
		{"", "Gül Dükany", "Gül Dükany"},
	}
	for _, c := range cases {
		if got := n.Normalize(c.in, c.key); got != c.want {
			t.Errorf("Normalize(%q, key %q) = %v, want %q", c.in, c.key, got, c.want)
		}
	}

	// Keys apply at any depth; null stays null.
	got := n.Normalize(mustJSON(t, `{"data":{"token":"x","remember_token":null,"items":[{"created_at":"2026-01-01 00:00:00"}]}}`), "")
	want := mustJSON(t, `{"data":{"token":"<token>","remember_token":null,"items":[{"created_at":"<datetime>"}]}}`)
	if d := Compare(want, got); len(d) > 0 {
		t.Errorf("nested normalize: %v", d)
	}
}

func TestCompare(t *testing.T) {
	exp := mustJSON(t, `{"a":1,"b":[1,2],"c":{"x":"y"},"d":null}`)
	if d := Compare(exp, mustJSON(t, `{"d":null,"c":{"x":"y"},"b":[1,2],"a":1.0}`)); len(d) != 0 {
		t.Errorf("equal documents differ: %v", d)
	}
	diffs := Compare(exp, mustJSON(t, `{"a":"1","b":[2,1],"c":{},"e":true,"d":null}`))
	paths := []string{}
	for _, d := range diffs {
		paths = append(paths, d.Path)
	}
	want := "$.a $.b[0] $.b[1] $.c.x $.e"
	if strings.Join(paths, " ") != want {
		t.Errorf("diff paths = %q, want %q", strings.Join(paths, " "), want)
	}
	if d := Compare(mustJSON(t, `[1,2,3]`), mustJSON(t, `[1,2]`)); len(d) != 1 || d[0].Path != "$" {
		t.Errorf("length diff = %v", d)
	}
	if d := Compare(mustJSON(t, `10000000000000000001`), mustJSON(t, `10000000000000000000`)); len(d) != 1 {
		t.Errorf("big numbers compared loosely: %v", d)
	}
}

func TestTimeShift(t *testing.T) {
	loc, _ := time.LoadLocation("Asia/Ashgabat")
	now := time.Date(2026, 10, 7, 12, 0, 0, 0, loc) // two days after recording
	ts, err := NewTimeShift("2026-10-05 12:00:00", "Asia/Ashgabat", now)
	if err != nil {
		t.Fatal(err)
	}
	cases := map[string]string{
		"2026-10-05 13:30:00":         "2026-10-07 13:30:00",
		"2026-10-05 13:30":            "2026-10-07 13:30",
		"2026-10-05T08:30:00.000000Z": "2026-10-07T08:30:00.000000Z",
		"2026-10-05T13:30:00+05:00":   "2026-10-07T13:30:00+05:00",
		"2026-10-05T13:30:00":         "2026-10-07T13:30:00",
		"2026-10-05":                  "2026-10-05", // dates (birthdays) stay
		"hello":                       "hello",
	}
	for in, want := range cases {
		if got := ts.String(in); got != want {
			t.Errorf("shift %q = %q, want %q", in, got, want)
		}
	}
	got := ts.Value(map[string]any{"at": []any{"2026-10-05 00:00:00", json.Number("1")}})
	if d := Compare(mustJSON(t, `{"at":["2026-10-07 00:00:00",1]}`), got); len(d) > 0 {
		t.Errorf("Value: %v", d)
	}
}

func TestFlatten(t *testing.T) {
	vals := url.Values{}
	flatten("", mustJSON(t, `{"name":"x","items":[{"id":1,"qty":2}],"tags":["a","b"],"gift":true,"note":null}`), vals)
	want := "gift=1&items%5B0%5D%5Bid%5D=1&items%5B0%5D%5Bqty%5D=2&name=x&note=&tags%5B0%5D=a&tags%5B1%5D=b"
	if got := vals.Encode(); got != want {
		t.Errorf("flatten = %s\nwant      %s", got, want)
	}
}

func TestBuildRequestMultipartCarriesJSONFields(t *testing.T) {
	f := &Fixture{Request: Request{
		Method:  "POST",
		Path:    "/api/shops",
		Headers: map[string]any{"Accept-Language": "en-us,en;q=0.5", "Content-Type": "application/json"},
		JSON:    json.RawMessage(`{"name":"Gül","region_id":5}`),
		Files:   []File{{Field: "image", Filename: "a.jpg", Mime: "image/jpeg", Base64: "AAEC"}},
	}}
	req, err := BuildRequest("http://example.test", f, "1|abc", nil)
	if err != nil {
		t.Fatal(err)
	}
	if err := req.ParseMultipartForm(1 << 20); err != nil {
		t.Fatal(err)
	}
	if req.FormValue("name") != "Gül" || req.FormValue("region_id") != "5" {
		t.Errorf("fields = %v", req.MultipartForm.Value)
	}
	if fh := req.MultipartForm.File["image"]; len(fh) != 1 || fh[0].Size != 3 {
		t.Errorf("file = %v", fh)
	}
	if req.Header.Get("Authorization") != "Bearer 1|abc" || req.Header.Get("Accept") != "application/json" || req.Header.Get("Accept-Language") == "" {
		t.Errorf("headers = %v", req.Header)
	}
}

func TestConfigMismatch(t *testing.T) {
	plain := &Fixture{}
	flagged := &Fixture{Config: map[string]any{"app.api_guest_browsing": false}}

	def := &Runner{}
	if def.configMismatch(plain) != "" || def.configMismatch(flagged) == "" {
		t.Error("default server must run plain fixtures only")
	}
	off := &Runner{Config: map[string]string{"app.api_guest_browsing": "false"}}
	if off.configMismatch(flagged) != "" || off.configMismatch(plain) == "" {
		t.Error("flagged server must run flagged fixtures only")
	}
}

func TestFixturesLoad(t *testing.T) {
	fs, err := LoadFixtures("fixtures")
	if err != nil {
		t.Fatal(err)
	}
	if len(fs) == 0 {
		t.Fatal("no fixtures under contract/fixtures")
	}
	for _, f := range fs {
		if f.Route == "" || f.Request.Method == "" || f.Response.Status == 0 {
			t.Errorf("%s: incomplete fixture", f.ID())
		}
	}
}
