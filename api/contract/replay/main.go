// Command replay sends every recorded contract fixture to a running API and
// reports differences.
//
//	go run ./contract/replay -base-url http://127.0.0.1:8000 \
//	    -dsn 'sowgatly:sowgatly@tcp(127.0.0.1:3306)/sowgatly_replay'
//
// See api/contract/README.md.
package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"flag"
	"fmt"
	"os"
	"regexp"
	"strings"

	_ "github.com/go-sql-driver/mysql"

	"github.com/Esca6585dev/sowgatly/api/contract"
)

func main() {
	var (
		baseURL  = flag.String("base-url", envOr("CONTRACT_BASE_URL", "http://127.0.0.1:8000"), "API base URL (no trailing /api)")
		dsn      = flag.String("dsn", envOr("CONTRACT_DSN", "sowgatly:sowgatly@tcp(127.0.0.1:3306)/sowgatly_replay"), "MySQL DSN of the database the API under test uses")
		dir      = flag.String("fixtures", envOr("CONTRACT_FIXTURES", "contract/fixtures"), "fixtures directory")
		run      = flag.String("run", "", "only fixtures whose id or route matches this regexp")
		verbose  = flag.Bool("v", false, "print passing fixtures too")
		report   = flag.String("report", "", "write a JSON report to this file")
		failFast = flag.Bool("fail-fast", false, "stop at the first failure")
		maxDiffs = flag.Int("max-diffs", 15, "differences printed per fixture")
		config   = configFlag{}
	)
	flag.Var(config, "config", "key=value: non-default config the server runs with (repeatable), e.g. app.api_guest_browsing=false")
	flag.Parse()

	fixtures, err := contract.LoadFixtures(*dir)
	exitOn(err)
	var filter *regexp.Regexp
	if *run != "" {
		filter = regexp.MustCompile(*run)
	}

	db, err := sql.Open("mysql", *dsn+dsnParams(*dsn))
	exitOn(err)
	defer func() { _ = db.Close() }()
	ctx := context.Background()
	exitOn(db.PingContext(ctx))

	runner, err := contract.NewRunner(ctx, *baseURL, db)
	exitOn(err)
	runner.Config = config

	counts := map[string]int{}
	var results []contract.Result
	for _, f := range fixtures {
		if filter != nil && !filter.MatchString(f.ID()) && !filter.MatchString(f.Route) {
			continue
		}
		res := runner.Run(ctx, f)
		results = append(results, res)
		counts[res.Status]++
		switch res.Status {
		case "pass":
			if *verbose {
				fmt.Printf("PASS  %s\n", res.ID)
			}
		case "skip":
			fmt.Printf("SKIP  %s (%s)\n", res.ID, res.Reason)
		default:
			fmt.Printf("%-5s %s  [%s]\n", strings.ToUpper(res.Status), res.ID, res.Route)
			if res.Reason != "" {
				fmt.Printf("      %s\n", res.Reason)
			}
			for i, d := range res.Diffs {
				if i == *maxDiffs {
					fmt.Printf("      … %d more\n", len(res.Diffs)-i)
					break
				}
				fmt.Printf("      %s\n", d)
			}
		}
		if *failFast && (res.Status == "fail" || res.Status == "error") {
			break
		}
	}

	total := len(results)
	fmt.Printf("\ncontract: %d passed, %d failed, %d errors, %d skipped (of %d)\n",
		counts["pass"], counts["fail"], counts["error"], counts["skip"], total)

	if *report != "" {
		b, _ := json.MarshalIndent(map[string]any{"counts": counts, "results": results}, "", "  ")
		exitOn(os.WriteFile(*report, b, 0o644))
	}
	if counts["fail"]+counts["error"] > 0 {
		os.Exit(1)
	}
}

func dsnParams(dsn string) string {
	if strings.Contains(dsn, "?") {
		return "&multiStatements=false"
	}
	return "?parseTime=false&charset=utf8mb4"
}

func envOr(k, def string) string {
	if v := os.Getenv(k); v != "" {
		return v
	}
	return def
}

func exitOn(err error) {
	if err != nil {
		fmt.Fprintln(os.Stderr, "replay:", err)
		os.Exit(2)
	}
}

// configFlag collects repeated -config key=value flags.
type configFlag map[string]string

func (c configFlag) String() string { return fmt.Sprint(map[string]string(c)) }

func (c configFlag) Set(s string) error {
	k, v, ok := strings.Cut(s, "=")
	if !ok || k == "" {
		return fmt.Errorf("want key=value, got %q", s)
	}
	c[k] = v
	return nil
}
