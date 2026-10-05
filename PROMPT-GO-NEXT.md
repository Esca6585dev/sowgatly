# Sowgatly: Laravel → Go (Fiber) + Next.js göçürişi

> **Nähili ulanmaly:** Täze Claude Code sessiýasyny açyp, `sowgatly` reposynyň `main`
> branchyny saýlaň we şeýle ýazyň:
>
> `PROMPT-GO-NEXT.md faýlyny oka we Phase 0-dan başlap tertip bilen ýerine ýetir.
> Her Phase-den soň testleri işlet, commit et we main-a push et.`
>
> **Her sessiýa üçin taýýar promptlar we ýagdaý tablisasy:**
> [`docs/migration/SESSIONS.md`](docs/migration/SESSIONS.md). Iş žurnaly: `docs/migration/LOG.md`.
>
> Bir sessiýada hemmesi gutarmaz: bu uly iş. Her täze sessiýada
> `PROMPT-GO-NEXT.md-de galan Phase-leri dowam et` diýiň. Laravel iň soňky Phase-e çenli
> işläp durýar, şonuň üçin saýt we mobil app göçüriş wagtynda bozulmaýar.
>
> Prompt iňlisçe, sebäbi model tehniki tabşyrygy iňlisçe has takyk ýerine ýetirýär.

---

## Context

Sowgatly is a flower and gift marketplace for Turkmenistan. Today everything runs on one
Laravel 10 app in this repository:

- **REST API** `/api/*` (≈120 routes, Sanctum bearer tokens, OTP login by SMS) used by the
  React Native app (`Esca6585dev/sowgatly-app-react-native`) and a Flutter app. Swagger spec:
  `storage/api-docs/api-docs.json`. Endpoint list: `README.md` → "API overview".
- **Admin panel** `/{locale}/admin/*` (≈100 routes, 18 sections, Blade, own design system in
  `public/admin/`, design notes and screenshots in `docs/admin-design/`).
- **Website**: only a placeholder today; the planned storefront is specified in `PROMPT.md` Phase 1.
- **MySQL** with 34 tables (`database/migrations`), Spatie roles/permissions for admins,
  images on the public disk, SMS via SMPP (TmCell) or Twilio (`app/Services`), FCM push
  (`app/Services/Fcm.php`, `app/Jobs/SendPushNotification.php`), in-app notifications,
  chats, waitlist, favorites collections, banners, home feed with cache.
- Behaviour is pinned by ≈165 feature tests in `tests/Feature/Api` and `tests/Feature/Admin`.

Goal: replace Laravel with a **Go (Fiber) API** and a **Next.js** web app (admin panel +
storefront), with **zero breaking changes for the mobile apps and zero data loss**.

## Target architecture

```
                ┌──────────── nginx ────────────┐
   mobile apps ─┤ /api/*        → api  (Go/Fiber, :8080)
   browsers   ──┤ /storage/*    → static files (same folder as today)
                │ /admin/*, /*  → web  (Next.js, :3000) ──► api (server-side fetch)
                └───────────────────────────────┘
   api ── MySQL (same database, same tables) ── Redis (cache, queues, rate limits)
```

Repository layout during the migration (monorepo, Laravel stays at the root until Phase 8):

```
api/        Go module  github.com/Esca6585dev/sowgatly/api
  cmd/api/              main.go
  internal/config, db (sqlc), http (fiber handlers, middleware), auth, otp, sms, push,
           storage, i18n, notify, cache, domain/* (catalog, cart, orders, chats …)
  migrations/           golang-migrate files (baseline = current schema)
  contract/             recorded Laravel responses + replay tests
web/        Next.js (App Router, TypeScript)
  app/(store)/[locale]/…   storefront
  app/admin/[locale]/…     admin panel
deploy/     docker-compose.yml, nginx.conf, systemd units
```

## Hard rules

1. **The API contract is frozen.** Same paths, methods, query/body field names, status codes,
   JSON shapes, field types (string vs number — e.g. decimals Laravel returns as strings stay
   strings), null vs missing, pagination `meta`/`links` shapes, error bodies
   (`{"success":false,"message":…}`, validation `422`), date formats. The contract tests from
   Phase 0 are the judge; a Go endpoint is "done" only when its contract tests pass.
2. **Same database, no destructive schema changes.** Go reads/writes the existing MySQL tables.
   New columns only additive with defaults. The Laravel migration history becomes a single
   baseline in `api/migrations` (Phase 1); never re-create tables.
3. **Existing sessions keep working.** Sanctum tokens look like `{id}|{plain}`; the
   `personal_access_tokens.token` column stores `sha256(plain)`. The Go auth middleware must
   accept these tokens unchanged (and update `last_used_at`). New tokens are issued in the same
   format and table. Admin passwords are bcrypt `$2y$…` — verify Go's bcrypt accepts the prefix
   (normalise `$2y$` → `$2a$` before comparing if needed); never force a password reset.
4. **Files stay where they are.** Image paths stored in the DB keep their meaning
   (`storage/...` on the public disk, plus older paths under `public/`); nginx serves them.
5. **Behaviour parity before improvement.** Port first, fix bugs in a separate commit with a test.
6. **Laravel keeps running** until Phase 8. Traffic moves path by path behind nginx.
7. Secrets only from environment variables (`.env` for local). Never commit keys or `.env`.
8. Every Phase ends with: Go tests green (`go test ./...`), contract tests green for the
   ported endpoints, `golangci-lint run` clean, `next build` + `next lint` clean for web work,
   commit (English message) and push to `main`.

## Stack

- Go (latest stable), **Fiber v3** (or v2 if a needed middleware is missing in v3 — document it),
  **sqlc** for type-safe MySQL queries, `golang-migrate`, `go-playground/validator`, `zerolog`,
  `golang.org/x/crypto/bcrypt`, Redis (`go-redis`) for cache, rate limiting and a job queue
  (`hibiken/asynq`) for push and SMS, an SMPP client (e.g. `github.com/fiorix/go-smpp`) and the
  Twilio REST API, Firebase Cloud Messaging HTTP v1 with a service-account file, OpenAPI via
  `swaggo/swag` (keep `/api/documentation` working), `testify` and a real MySQL for tests.
- Next.js (latest stable, App Router, TypeScript, React Server Components), Tailwind CSS with
  the design tokens from `public/admin/admin.css` (`--brand-1 #FF6A00`, `--brand-2 #FF2A00`,
  light/dark variables), `next-intl` with **tm** (default), **ru**, **en** — migrate the strings from
  `resources/lang/*.json`, `zod` for forms, server actions or route handlers that call the Go API
  server-side. No client-side secrets.

---

## Phase 0 — Freeze the API contract (in Laravel, before writing Go)

1. Add a test-only middleware or a PHPUnit extension that, while `php artisan test tests/Feature/Api`
   runs, records every request/response pair (method, path, query, body, auth role,
   status, JSON body) into `api/contract/fixtures/<endpoint>/<case>.json`. Replace volatile
   values (ids, timestamps, tokens, random codes) with typed placeholders
   (`"<int>"`, `"<datetime>"`, `"<token>"`) so fixtures are comparable.
2. Add a small Go tool `api/contract/replay` that seeds a database from a fixture's seed
   section, sends the recorded request to `BASE_URL` and compares the JSON structure, types and
   stable values. Run it against Laravel first — it must pass 100 % before any Go code exists.
3. Add any missing coverage: every route in `php artisan route:list --path=api` must have at
   least one fixture (happy path + one validation/authorization failure for writes).
4. Write `api/contract/README.md`: how to record, how to replay, what "pass" means.

## Phase 1 — Go skeleton

1. `api/` module, config from env, structured logging, graceful shutdown, `/healthz`.
2. MySQL connection pool; `golang-migrate` baseline generated from the current schema
   (`mysqldump --no-data`), marked as already applied on existing databases.
3. sqlc set-up with the baseline schema; first queries for users and tokens.
4. Middleware: request id, recover, CORS (same origins as Laravel `config/cors.php`), locale
   from `Accept-Language`/URL, JSON error handler matching Laravel's shapes, rate limiting
   identical to the Laravel `throttle:` values per route.
5. Sanctum-compatible bearer auth (rule 3), "optional auth" for guest-browsable routes, and the
   `check.token` behaviour. Unit tests with real tokens created by Laravel.
6. Validation helper that renders Laravel-style 422 bodies (first message in `message`).
7. File storage helper (public disk path ↔ URL exactly like `asset()`/`Storage::url()` today).
8. docker-compose for local dev: mysql, redis, api, laravel (php-fpm) and nginx routing
   `/api/healthz` to Go and everything else to Laravel.

## Phase 2 — Port the read-only catalog

Categories, subcategories, brands, compositions, regions (incl. `selfAndDescendantIds`),
shops (index/show), products (search with every filter and sort, by category, show), reviews
index, banners, home feed (sections, popular, delivery_today, cache + invalidation),
payment methods. Move these paths to Go in nginx once their contract tests pass.

## Phase 3 — Auth and profile

OTP generate/login/register (same 4-digit codes, expiry, `OTP_DEBUG_CODE`, rate limits,
`TurkmenistanPhoneNumber` rule), SMS sending through the queue (SMPP primary, Twilio fallback,
log-only when unconfigured), logout, devices/FCM tokens, `users/me` GET/PUT/POST (multipart and
base64 avatar, birth date), `DELETE users/me/image`, user addresses CRUD.

## Phase 4 — Shopping

Cart (add/update/remove; a cart may hold several shops), favorites + favorite collections, waitlist (and the
"back in stock" notification trigger — port the Product observer logic into the product write
paths), checkout (`POST /orders`: one order per shop with `order` + `orders` in the response, fulfillment, per-shop delivery fee, payment method/bank, totals, stock
decrement in one transaction), orders list/search/show/cancel with restock, reviews create
(three criteria, order link, buyer-only rule).

## Phase 5 — Shop owners, chats, notifications

Shop CRUD for owners (one shop per user, image upload), owner product CRUD, shop orders and
status transitions (`Order::TRANSITIONS` incl. `delivering`), chats for customers and shops
(threads, messages, unread counters, read, throttling, HTML stripping), in-app notifications
(list with localized title/body, unread count, mark read), FCM push via the queue, shop
applications. After this Phase **all `/api/*` traffic goes to Go**; Laravel serves only the admin.

## Phase 6 — Admin API in Go

JSON endpoints under `/admin-api/*` for every admin section (dashboard KPIs, orders, chats,
shop applications, messages, products, carts, shops, addresses, categories, attributes,
banners, regions, users + CSV export, admins, roles, permissions). Admin login with bcrypt
against the `admins` table, session as an httpOnly, Secure, SameSite=Lax cookie (signed JWT or
Redis session), CSRF protection for cookie-authenticated writes, login rate limit 10/min.
Authorisation from the existing Spatie tables (`roles`, `permissions`, `model_has_roles`,
`role_has_permissions`, guard `admin`). Port the admin feature tests to Go.

## Phase 7 — Next.js web: admin panel, then storefront

1. **Admin** at `/admin/[locale]/…` with the current look: copy the layout, sidebar
   (collapsible, remembered), light/dark theme (OS default, remembered), mobile drawer, toasts,
   confirm dialog, tables with search/filters/pagination, forms with inline errors, image
   upload previews. Use `docs/admin-design/*.png` and the live Laravel admin as the reference;
   pages must match section by section. Playwright tests: login, one CRUD per section, theme
   and sidebar persistence, mobile layout.
2. **Storefront** at `/[locale]/…` implementing `PROMPT.md` Phase 1 (Flowwow-style: city
   selector, catalogue with filters, product page, shop page, cart, checkout, orders, account,
   OTP login, static pages) with SSR for SEO (metadata, Open Graph, sitemap.xml, robots.txt)
   and the gift-box branding (`public/img/logo/*.svg`).
3. Switch nginx: `/admin` → Next.js admin, `/` → storefront. Keep Laravel admin reachable
   at a hidden path for two weeks as a fallback.

## Phase 8 — Cut-over and remove Laravel

1. Production checklist: backups, env vars, `storage` folder mounted into nginx, Redis,
   queue worker running, FCM and SMS credentials, monitoring (health checks, error logs),
   rollback plan (nginx routes back to Laravel).
2. Run both stacks side by side for at least one week with all traffic on Go/Next.js.
3. Delete the Laravel application (PHP code, composer files, Blade views, Vite config), move
   `api/` and `web/` to the root if wanted, update `README.md`, `CLAUDE.md`, `.claude/agents/*`
   (Go and Next.js commands instead of `php artisan`), and keep `storage/` data in place.

## Deployment (`deploy/`)

docker-compose with `mysql`, `redis`, `api`, `worker` (asynq), `web`, `nginx`; health checks;
volumes for MySQL data and the uploads folder; `.env.example` for each service. Also provide
systemd units for a non-Docker server.

## Final report after each session

What was ported (endpoint list), contract test pass rate (n/total), what still runs on Laravel,
known differences and why, next Phase to start.
