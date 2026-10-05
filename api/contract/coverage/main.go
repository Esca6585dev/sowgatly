// Command coverage checks that every API route has contract fixtures: at
// least one 2xx response, and for write routes at least one failure too: a
// 4xx, or a 2xx whose body says {"success": false} (the OTP routes answer
// failures that way).
//
//	php artisan route:list --path=api --json | go run ./contract/coverage
//
// It exits 1 and lists the gaps when a route is not covered.
package main

import (
	"encoding/json"
	"flag"
	"fmt"
	"os"
	"regexp"
	"sort"
	"strings"

	"github.com/Esca6585dev/sowgatly/api/contract"
)

type route struct {
	Method string `json:"method"`
	URI    string `json:"uri"`
}

func main() {
	dir := flag.String("fixtures", "contract/fixtures", "fixtures directory")
	routesFile := flag.String("routes", "-", "output of `php artisan route:list --path=api --json` (- for stdin)")
	ignore := flag.String("ignore", `documentation|telescope|oauth2-callback`, "skip routes whose URI matches this regexp")
	flag.Parse()

	in := os.Stdin
	if *routesFile != "-" {
		f, err := os.Open(*routesFile)
		exitOn(err)
		defer func() { _ = f.Close() }()
		in = f
	}
	var routes []route
	exitOn(json.NewDecoder(in).Decode(&routes))

	fixtures, err := contract.LoadFixtures(*dir)
	exitOn(err)
	statuses := map[string][]int{}
	softFail := map[string]bool{}
	for _, f := range fixtures {
		if f.Replayable {
			statuses[f.Route] = append(statuses[f.Route], f.Response.Status)
			var body struct {
				Success *bool `json:"success"`
			}
			if json.Unmarshal(f.Response.JSON, &body) == nil && body.Success != nil && !*body.Success {
				softFail[f.Route] = true
			}
		}
	}

	skip := regexp.MustCompile(*ignore)
	var gaps []string
	total := 0
	for _, r := range routes {
		if skip.MatchString(r.URI) {
			continue
		}
		for _, m := range strings.Split(r.Method, "|") {
			if m == "HEAD" {
				continue
			}
			total++
			key := m + " " + r.URI
			ok2xx, ok4xx := false, softFail[key]
			for _, s := range statuses[key] {
				ok2xx = ok2xx || (s >= 200 && s < 300)
				ok4xx = ok4xx || (s >= 400 && s < 500)
			}
			if !ok2xx {
				gaps = append(gaps, fmt.Sprintf("%-45s no 2xx fixture %v", key, statuses[key]))
			}
			if m != "GET" && !ok4xx {
				gaps = append(gaps, fmt.Sprintf("%-45s no failure fixture %v", key, statuses[key]))
			}
		}
	}
	sort.Strings(gaps)
	for _, g := range gaps {
		fmt.Println(g)
	}
	fmt.Printf("coverage: %d routes, %d fixtures, %d gaps\n", total, len(fixtures), len(gaps))
	if len(gaps) > 0 {
		os.Exit(1)
	}
}

func exitOn(err error) {
	if err != nil {
		fmt.Fprintln(os.Stderr, "coverage:", err)
		os.Exit(2)
	}
}
