package contract

import (
	"context"
	"crypto/rand"
	"crypto/sha256"
	"database/sql"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"sort"
	"strings"
	"time"
)

// Seeder loads a fixture's database state into a MySQL database whose
// schema matches the application's (run the migrations first).
type Seeder struct {
	DB     *sql.DB
	tables []string
}

// NewSeeder lists the tables once; "migrations" is never touched.
func NewSeeder(ctx context.Context, db *sql.DB) (*Seeder, error) {
	rows, err := db.QueryContext(ctx, "SHOW TABLES")
	if err != nil {
		return nil, err
	}
	defer func() { _ = rows.Close() }()
	s := &Seeder{DB: db}
	for rows.Next() {
		var t string
		if err := rows.Scan(&t); err != nil {
			return nil, err
		}
		if t != "migrations" {
			s.tables = append(s.tables, t)
		}
	}
	return s, rows.Err()
}

// Load empties every table, inserts the fixture rows (datetimes shifted),
// restores AUTO_INCREMENT counters and, when the fixture was recorded with
// a signed-in user, creates a Sanctum token and returns the bearer value.
func (s *Seeder) Load(ctx context.Context, f *Fixture, shift *TimeShift) (bearer string, err error) {
	conn, err := s.DB.Conn(ctx)
	if err != nil {
		return "", err
	}
	defer func() { _ = conn.Close() }()

	if _, err := conn.ExecContext(ctx, "SET FOREIGN_KEY_CHECKS=0"); err != nil {
		return "", err
	}
	defer conn.ExecContext(context.Background(), "SET FOREIGN_KEY_CHECKS=1") //nolint:errcheck

	for _, t := range s.tables {
		if _, err := conn.ExecContext(ctx, "TRUNCATE TABLE `"+t+"`"); err != nil {
			return "", fmt.Errorf("truncate %s: %w", t, err)
		}
	}

	names := make([]string, 0, len(f.Seed.Tables))
	for t := range f.Seed.Tables {
		names = append(names, t)
	}
	sort.Strings(names)
	for _, t := range names {
		for _, row := range f.Seed.Tables[t] {
			if err := insertRow(ctx, conn, t, row, shift); err != nil {
				return "", fmt.Errorf("seed %s: %w", t, err)
			}
		}
	}

	for t, next := range f.Seed.AutoIncrement {
		if next <= 1 {
			continue
		}
		if _, err := conn.ExecContext(ctx, fmt.Sprintf("ALTER TABLE `%s` AUTO_INCREMENT = %d", t, next)); err != nil {
			return "", fmt.Errorf("auto_increment %s: %w", t, err)
		}
	}

	if f.Auth != nil && f.Auth.UserID > 0 {
		return createToken(ctx, conn, f.Auth.UserID)
	}
	return "", nil
}

func insertRow(ctx context.Context, conn *sql.Conn, table string, row map[string]any, shift *TimeShift) error {
	cols := make([]string, 0, len(row))
	for c := range row {
		cols = append(cols, c)
	}
	sort.Strings(cols)
	quoted := make([]string, len(cols))
	marks := make([]string, len(cols))
	args := make([]any, len(cols))
	for i, c := range cols {
		quoted[i] = "`" + c + "`"
		marks[i] = "?"
		args[i] = sqlValue(row[c], shift)
	}
	q := fmt.Sprintf("INSERT INTO `%s` (%s) VALUES (%s)", table, strings.Join(quoted, ","), strings.Join(marks, ","))
	_, err := conn.ExecContext(ctx, q, args...)
	return err
}

func sqlValue(v any, shift *TimeShift) any {
	switch t := v.(type) {
	case nil:
		return nil
	case json.Number:
		return t.String()
	case bool:
		if t {
			return 1
		}
		return 0
	case string:
		return shift.String(t)
	default:
		// Nested JSON (should not happen: MySQL returns JSON columns as text).
		b, _ := json.Marshal(t)
		return string(b)
	}
}

// createToken inserts a Sanctum personal access token for the user and
// returns "id|plain", the value Laravel (and the Go API) accept as bearer.
func createToken(ctx context.Context, conn *sql.Conn, userID int64) (string, error) {
	buf := make([]byte, 20)
	if _, err := rand.Read(buf); err != nil {
		return "", err
	}
	plain := hex.EncodeToString(buf) // 40 chars
	sum := sha256.Sum256([]byte(plain))
	now := time.Now().UTC().Format("2006-01-02 15:04:05")
	res, err := conn.ExecContext(ctx,
		"INSERT INTO personal_access_tokens (tokenable_type, tokenable_id, name, token, abilities, created_at, updated_at) VALUES (?,?,?,?,?,?,?)",
		`App\Models\User`, userID, "contract-replay", hex.EncodeToString(sum[:]), `["*"]`, now, now)
	if err != nil {
		return "", fmt.Errorf("create token: %w", err)
	}
	id, err := res.LastInsertId()
	if err != nil {
		return "", err
	}
	return fmt.Sprintf("%d|%s", id, plain), nil
}
