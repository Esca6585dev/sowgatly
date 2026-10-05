package contract

import (
	"bytes"
	"encoding/base64"
	"encoding/json"
	"fmt"
	"io"
	"mime/multipart"
	"net/http"
	"net/textproto"
	"net/url"
	"sort"
	"strings"
)

// BuildRequest turns a fixture request into an *http.Request for baseURL.
// Datetimes in query, form and JSON bodies are shifted.
func BuildRequest(baseURL string, f *Fixture, bearer string, shift *TimeShift) (*http.Request, error) {
	r := f.Request
	u, err := url.Parse(strings.TrimRight(baseURL, "/") + r.Path)
	if err != nil {
		return nil, err
	}
	q := url.Values{}
	flatten("", shift.Value(r.Query), q)
	u.RawQuery = q.Encode()

	var body io.Reader
	contentType := ""
	switch {
	case len(r.Files) > 0:
		buf := &bytes.Buffer{}
		w := multipart.NewWriter(buf)
		fields := url.Values{}
		flatten("", shift.Value(r.Form), fields)
		if len(r.JSON) > 0 {
			// The test sent its fields as an array next to the files;
			// Laravel turned them into multipart fields.
			v, err := decodeJSON(r.JSON)
			if err != nil {
				return nil, fmt.Errorf("json body: %w", err)
			}
			flatten("", shift.Value(v), fields)
		}
		keys := make([]string, 0, len(fields))
		for k := range fields {
			keys = append(keys, k)
		}
		sort.Strings(keys)
		for _, k := range keys {
			for _, v := range fields[k] {
				if err := w.WriteField(k, v); err != nil {
					return nil, err
				}
			}
		}
		for _, file := range r.Files {
			data, err := base64.StdEncoding.DecodeString(file.Base64)
			if err != nil {
				return nil, fmt.Errorf("file %s: %w", file.Field, err)
			}
			h := make(textproto.MIMEHeader)
			h.Set("Content-Disposition", fmt.Sprintf(`form-data; name=%q; filename=%q`, file.Field, file.Filename))
			h.Set("Content-Type", file.Mime)
			part, err := w.CreatePart(h)
			if err != nil {
				return nil, err
			}
			if _, err := part.Write(data); err != nil {
				return nil, err
			}
		}
		if err := w.Close(); err != nil {
			return nil, err
		}
		body, contentType = buf, w.FormDataContentType()
	case len(r.JSON) > 0:
		v, err := decodeJSON(r.JSON)
		if err != nil {
			return nil, err
		}
		b, err := json.Marshal(shift.Value(v))
		if err != nil {
			return nil, err
		}
		body, contentType = bytes.NewReader(b), "application/json"
	case r.Raw != nil:
		body = strings.NewReader(*r.Raw)
	case len(r.Form) > 0:
		vals := url.Values{}
		flatten("", shift.Value(r.Form), vals)
		body, contentType = strings.NewReader(vals.Encode()), "application/x-www-form-urlencoded"
	}

	req, err := http.NewRequest(r.Method, u.String(), body)
	if err != nil {
		return nil, err
	}
	for k, v := range r.Headers {
		if strings.EqualFold(k, "Content-Type") || strings.EqualFold(k, "Authorization") && bearer != "" {
			continue
		}
		req.Header.Set(k, fmt.Sprint(v))
	}
	if contentType != "" {
		req.Header.Set("Content-Type", contentType)
	} else if ct, ok := r.Headers["Content-Type"]; ok {
		req.Header.Set("Content-Type", fmt.Sprint(ct))
	}
	if req.Header.Get("Accept") == "" {
		req.Header.Set("Accept", "application/json")
	}
	if bearer != "" {
		req.Header.Set("Authorization", "Bearer "+bearer)
	}
	return req, nil
}

// flatten encodes nested maps/slices the way PHP parses them:
// a[b]=1, a[]=2 (lists keep their indexes: a[0]=2).
func flatten(prefix string, v any, out url.Values) {
	switch t := v.(type) {
	case map[string]any:
		keys := make([]string, 0, len(t))
		for k := range t {
			keys = append(keys, k)
		}
		sort.Strings(keys)
		for _, k := range keys {
			name := k
			if prefix != "" {
				name = prefix + "[" + k + "]"
			}
			flatten(name, t[k], out)
		}
	case []any:
		for i, val := range t {
			flatten(fmt.Sprintf("%s[%d]", prefix, i), val, out)
		}
	case nil:
		out.Add(prefix, "")
	case bool:
		if t {
			out.Add(prefix, "1")
		} else {
			out.Add(prefix, "0")
		}
	default:
		out.Add(prefix, fmt.Sprint(t))
	}
}

func decodeJSON(raw []byte) (any, error) {
	var v any
	dec := json.NewDecoder(bytes.NewReader(raw))
	dec.UseNumber()
	err := dec.Decode(&v)
	return v, err
}
