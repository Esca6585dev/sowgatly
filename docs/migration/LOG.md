# Migration log

Each session adds a dated entry: what was ported, contract tests passing (n/total), what still
runs on Laravel, open issues.

## 2026-10-05 — plan

- Plan in `PROMPT-GO-NEXT.md`, session prompts in `docs/migration/SESSIONS.md`.
- Nothing ported yet; Laravel serves the API (≈120 routes), the admin panel (≈100 routes) and the website.

## 2026-10-05 — Session 1: Phase 0, API contract frozen

- Recorder: `tests/Contract/RecordsContract.php` (trait on `Tests\TestCase`, active with
  `CONTRACT_RECORD=1`) writes every `/api` request of `tests/Feature/Api` to
  `api/contract/fixtures/` with the DB rows + AUTO_INCREMENT before the request, the signed-in
  user, changed config, request (incl. uploads) and normalised response. Rules for volatile
  values (datetimes, tokens, URLs, random file names) in `api/contract/normalize.json`, shared
  by PHP and Go.
- Go module `api/` (`github.com/Esca6585dev/sowgatly/api`, Go 1.24): `contract` package,
  `go run ./contract/replay` CLI, `TestReplay` Go test, `go run ./contract/coverage`.
- Coverage: all 82 API routes (documentation/Telescope/OAuth callback excluded) have a 2xx
  fixture, every write route a failure fixture; 26 new tests in
  `ContractCoverageTest` / `ContractGuestFailuresTest`.
- Contract pass rate against Laravel (`php artisan serve` + MariaDB 10.11): **248/248**
  (246 on the default config, 2 with `APP_API_GUEST_BROWSING=false`).
- Nothing ported yet; Laravel still serves everything.
- Open issues found while covering routes (not fixed, current behaviour is what the contract
  holds):
  - `POST /api/login` with header `Device: Mobile` returns 500 for a user without a device row:
    `Device::updateOrCreate` sets `token`, but the column is `device_token`.
  - `otp/generate`, `login`, `register` answer failures with 200 + `success:false`, and crash
    with 500 when `phone_number` is not a string (`TurkmenistanPhoneNumber` calls preg_match on
    it). No 500 is recorded in the contract.
  - `DELETE /api/shops/{shop}` answers with a `status` key, `PATCH` with `success`.
- Rule from now on: any Laravel change to `/api` behaviour re-records the fixtures in the same
  commit (README "Record"), so the contract stays the truth for the Go port.
