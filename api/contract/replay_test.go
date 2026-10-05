package contract

import (
	"context"
	"database/sql"
	"os"
	"strings"
	"testing"

	_ "github.com/go-sql-driver/mysql"
)

// TestReplay replays every fixture against a running API. It needs
// CONTRACT_BASE_URL (e.g. http://127.0.0.1:8010) and CONTRACT_DSN (the
// MySQL database that API uses); without them it is skipped. Optional
// CONTRACT_CONFIG="key=value,key=value" declares non-default server config.
//
//	CONTRACT_BASE_URL=http://127.0.0.1:8010 \
//	CONTRACT_DSN='sowgatly:sowgatly@tcp(127.0.0.1:3306)/sowgatly_replay' \
//	go test ./contract -run TestReplay
func TestReplay(t *testing.T) {
	base, dsn := os.Getenv("CONTRACT_BASE_URL"), os.Getenv("CONTRACT_DSN")
	if base == "" || dsn == "" {
		t.Skip("set CONTRACT_BASE_URL and CONTRACT_DSN to replay the fixtures")
	}
	if !strings.Contains(dsn, "?") {
		dsn += "?parseTime=false&charset=utf8mb4"
	}
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = db.Close() }()
	ctx := context.Background()

	runner, err := NewRunner(ctx, base, db)
	if err != nil {
		t.Fatal(err)
	}
	runner.Config = map[string]string{}
	for _, kv := range strings.Split(os.Getenv("CONTRACT_CONFIG"), ",") {
		if k, v, ok := strings.Cut(kv, "="); ok {
			runner.Config[strings.TrimSpace(k)] = strings.TrimSpace(v)
		}
	}

	fixtures, err := LoadFixtures("fixtures")
	if err != nil {
		t.Fatal(err)
	}
	for _, f := range fixtures {
		t.Run(f.ID(), func(t *testing.T) {
			res := runner.Run(ctx, f)
			switch res.Status {
			case "skip":
				t.Skip(res.Reason)
			case "pass":
			default:
				t.Errorf("%s %s", res.Status, res.Reason)
				for _, d := range res.Diffs {
					t.Log(d)
				}
			}
		})
	}
}
