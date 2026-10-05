# API contract (Phase 0)

The JSON API that the React Native and Flutter apps use is frozen here as
**fixtures**: recorded request/response pairs, each with the database state
right before the request. Any server (Laravel today, the Go Fiber API later)
must answer every fixture the same way. This is the acceptance test for the
migration in `PROMPT-GO-NEXT.md`.

```
api/contract/
  normalize.json        volatile-value rules, shared by PHP and Go
  fixtures/<METHOD>-<route>/<Test>__<method>__<n>.json
  *.go                  loader, seeder, request builder, comparer, runner
  replay/               go run ./contract/replay   (CLI)
  coverage/             go run ./contract/coverage (route coverage check)
tests/Contract/         PHP recorder (RecordsContract trait, Normalizer)
```

## What a fixture holds

| Field | Meaning |
|---|---|
| `route` | Laravel route, e.g. `POST api/orders` |
| `recorded_at`, `timezone` | when it was recorded (app timezone, `Asia/Ashgabat`) |
| `auth` | `{"type":"bearer","user_id":7}` or `null` (guest) |
| `config` | config the test changed from the defaults (e.g. `app.api_guest_browsing: false`), usually `{}` |
| `request` | method, path, query, headers, and one of `json` / `form` / `raw`, plus `files` (base64) |
| `response` | status, content type, normalised `json` (or `body_sha1` for non-JSON) |
| `seed` | every table's rows (except `migrations`) and its `AUTO_INCREMENT` |
| `replayable` | `false` for 429 responses (rate-limit state lives in the cache, not the DB) |

## How a replay works

For each fixture the replay:

1. truncates every table of the target database and inserts the `seed` rows,
   restoring each table's `AUTO_INCREMENT` (so new rows get the same ids);
2. shifts every recorded datetime (seed rows and request body) by
   *now − recorded_at*, so "an hour ago", "tomorrow 15:00" and `after:now`
   rules keep their meaning; dates without a time (birthdays) are not shifted;
3. if `auth` is set, inserts a Sanctum token for that user into
   `personal_access_tokens` and sends `Authorization: Bearer <id>|<plain>`;
4. sends the request (multipart when the fixture has files);
5. normalises the response with `normalize.json` and compares it.

**Pass** means: same status code, same content type, and the same JSON:
same keys, same types and values (numbers compared by value, `1` = `1.0`),
object key order ignored, array order significant. Fixtures keep the
response exactly as Laravel produced it (key order, decimals as strings,
`null`s), so they also document the shape. Ids are not placeholders: the
seed restores `AUTO_INCREMENT`, so a new row gets the same id on replay and
ids are compared as values. Before comparing,
volatile values become placeholders on both sides:

| Value | Placeholder |
|---|---|
| datetimes (`2026-10-05 12:00:00`, ISO 8601) | `<datetime>` |
| Sanctum tokens, keys `token`, `access_token`, `device_token`, … | `<token>` |
| absolute URLs | `<url>` + path (`<url>/storage/shops/<file>`) |
| random file names (`Str::random`, `uniqid`) in URLs and `/storage/…` paths | `<file>` |

## Setup (once)

MariaDB (or MySQL 8) is needed for recording and for the replay database:

```bash
sudo apt-get update
sudo apt-get install -y mariadb-server mariadb-client
sudo service mariadb start

sudo mariadb <<'SQL'
CREATE DATABASE IF NOT EXISTS sowgatly_test   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS sowgatly_replay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'sowgatly'@'localhost' IDENTIFIED BY 'sowgatly';
CREATE USER IF NOT EXISTS 'sowgatly'@'127.0.0.1' IDENTIFIED BY 'sowgatly';
GRANT ALL PRIVILEGES ON sowgatly_test.*   TO 'sowgatly'@'localhost', 'sowgatly'@'127.0.0.1';
GRANT ALL PRIVILEGES ON sowgatly_replay.* TO 'sowgatly'@'localhost', 'sowgatly'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
```

Go 1.24+ is needed for the replay (`cd api && go mod download`).

## 1. Record

The recorder is a trait on `Tests\TestCase`; it only does something when
`CONTRACT_RECORD` is set. Record on MySQL/MariaDB (not SQLite) so ids,
types and `AUTO_INCREMENT` match production, with `APP_DEBUG=false` so error
responses carry no stack traces:

```bash
rm -rf api/contract/fixtures/*
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=sowgatly_test \
DB_USERNAME=sowgatly DB_PASSWORD=sowgatly APP_DEBUG=false CONTRACT_RECORD=1 \
php artisan test tests/Feature/Api
```

Every `/api/*` request a test makes becomes one fixture. Re-recording
rewrites all fixtures; review the diff before committing.

## 2. Start the server under test

The replay server needs the same environment the tests run with
(`phpunit.xml`), but on its own database:

```bash
export APP_ENV=testing APP_DEBUG=false BCRYPT_ROUNDS=4 CACHE_DRIVER=array \
       MAIL_MAILER=array QUEUE_CONNECTION=sync SESSION_DRIVER=array \
       TELESCOPE_ENABLED=false OTP_DEBUG_CODE=0000
export DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 \
       DB_DATABASE=sowgatly_replay DB_USERNAME=sowgatly DB_PASSWORD=sowgatly

php artisan migrate:fresh --force          # schema only; the replay fills the rows
php artisan serve --host=127.0.0.1 --port=8010
```

`CACHE_DRIVER=array` matters: `php artisan serve` handles each request in a
fresh process, so rate limits never carry over between fixtures.

The Go API (later phases) is started the same way against `sowgatly_replay`.

## 3. Replay

```bash
cd api
go run ./contract/replay -base-url http://127.0.0.1:8010 \
  -dsn 'sowgatly:sowgatly@tcp(127.0.0.1:3306)/sowgatly_replay'
# contract: 246 passed, 0 failed, 0 errors, 2 skipped (of 248)
```

Flags: `-run <regexp>` (fixture id or route), `-v` (print passes),
`-report out.json`, `-fail-fast`, `-max-diffs N`, `-fixtures DIR`,
`-config key=value`. `CONTRACT_BASE_URL`, `CONTRACT_DSN` and
`CONTRACT_FIXTURES` work instead of the flags.

**The replay truncates every table of the `-dsn` database.** Never point
it at a database you care about.

### Fixtures that need other config

A fixture whose `config` is not `{}` only runs against a server started with
that config, declared with `-config`; every other fixture is skipped on such
a server. Today two fixtures need guest browsing off:

```bash
APP_API_GUEST_BROWSING=false php artisan serve --host=127.0.0.1 --port=8011   # + the env above
go run ./contract/replay -base-url http://127.0.0.1:8011 -config app.api_guest_browsing=false
# contract: 2 passed, 0 failed, 0 errors, 246 skipped (of 248)
```

Both runs together cover all fixtures.

### As a Go test

```bash
go test ./...                                   # unit tests; TestReplay skips
CONTRACT_BASE_URL=http://127.0.0.1:8010 \
CONTRACT_DSN='sowgatly:sowgatly@tcp(127.0.0.1:3306)/sowgatly_replay' \
go test ./contract -run TestReplay              # one subtest per fixture
# CONTRACT_CONFIG=app.api_guest_browsing=false for the second server
```

## Coverage

Every API route needs at least one 2xx fixture, and every write route
(POST/PUT/PATCH/DELETE) at least one failure fixture (a 4xx, or a 2xx with `"success": false`, which is how the OTP routes answer failures):

```bash
php artisan route:list --path=api --json | (cd api && go run ./contract/coverage)
# coverage: 82 routes, 248 fixtures, 0 gaps
```

`api/documentation`, Telescope and the OAuth2 callback are not part of the
contract. Fixtures with `replayable: false` (429s) do not count.

## Adding a fixture

1. Write a normal feature test in `tests/Feature/Api/` that makes the
   request (`$this->getJson(...)`, `postJson`, `actingAs` / `ApiTestCase`).
   Keep the response determined by the database and the request: pin
   factory values the response depends on, and do not rely on randomness
   created during the request (random file names are fine, they are
   normalised).
2. Record (step 1), replay (step 3), run the coverage check.
3. Commit the test and the new or changed files under `fixtures/`.

If a value is volatile for a good reason (a new random token field), add a
rule to `normalize.json`; the PHP recorder and the Go replay both read it.

## Before every push (migration sessions)

```bash
cd api && go test ./... && golangci-lint run && go run ./contract/replay
cd .. && php artisan test
```
