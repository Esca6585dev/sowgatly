# Go + Next.js göçürişi — sessiýa promptlary

Esasy meýilnama: [`PROMPT-GO-NEXT.md`](../../PROMPT-GO-NEXT.md). Bu faýlda her sessiýa üçin
**göni goýup boljak** prompt bar. Tertip bilen işlediň: her sessiýa öňküsiniň `main`-a
push eden işine daýanýar.

**Her sessiýada:**
1. Täze Claude Code sessiýasyny açyň, `Esca6585dev/sowgatly` reposyny saýlaň (3-nji we 9-njy
   sessiýalarda mobil repony hem goşuň).
2. Ilki modeli saýlaň (tablisada görkezilen): `/model claude-opus-5-5` ýa-da `/model claude-fable-5-1`.
3. Aşakdaky prompty doly göçürip goýuň.
4. Sessiýa gutaranda aşakdaky **Ýagdaý** tablisasy täzelenen bolmaly. Ony barlaň, soň indiki sessiýa geçiň.

Bir sessiýa gutarman galsa, şol sessiýanyň promptyny täze sessiýada gaýtadan goýuň.
Prompt "galanyny dowam et" diýip işleýär.

## Ýagdaý

| # | Sessiýa | Model | Ýagdaýy |
|---|---|---|---|
| 1 | Phase 0 — API kontraktyny doňdurmak | Fable 5.1 | ⏳ başlanmady |
| 2 | Phase 1 — Go esasy gurluşy, Sanctum-a gabat gelýän auth | Fable 5.1 | ⏳ |
| 3 | Phase 2 — Katalog (diňe okamak) | Opus 5.5 | ⏳ |
| 4 | Phase 3 — OTP giriş we profil | Opus 5.5 | ⏳ |
| 5 | Phase 4 — Sebet, sargyt, synlar | Opus 5.5 | ⏳ |
| 6 | Phase 5 — Dükan eýeleri, çatlar, bildirişler, push | Opus 5.5 | ⏳ |
| 7 | Phase 6 — Admin API | Opus 5.5 | ⏳ |
| 8 | Phase 7a — Next.js admin panel | Opus 5.5 | ⏳ |
| 9 | Phase 7b — Next.js dükan saýty | Opus 5.5 | ⏳ |
| 10 | Phase 8 — Deploy, geçiş, Laravel-i aýyrmak | Fable 5.1 | ⏳ |

Ýagdaý belgileri: ⏳ başlanmady · 🔄 dowam edýär · ✅ gutardy.

---

## Ähli sessiýalar üçin umumy düzgün (her promptuň içinde eýýäm bar)

```
Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.
```

---

## 1 — Phase 0: API kontraktyny doňdurmak · Fable 5.1

```
Session 1 of the Go + Next.js migration: PROMPT-GO-NEXT.md Phase 0 (freeze the API contract).

Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.

Deliver:
1. A recorder that captures every request/response of `php artisan test tests/Feature/Api` into api/contract/fixtures/<method>-<path-slug>/<case>.json with the DB seed needed to replay it, auth role, request, status and normalised response (volatile values → typed placeholders such as "<int>", "<datetime>", "<token>", "<url>"). Keep decimals-as-strings, nulls and key order visible in the fixture.
2. Coverage: every route from `php artisan route:list --path=api` (except documentation/telescope) has ≥1 happy-path fixture and, for writes, ≥1 failure fixture (422 or 401/403/404). Add Laravel tests where coverage is missing.
3. api/contract/replay: a Go CLI + `go test` package that loads a fixture, seeds a MySQL test database, calls BASE_URL and compares structure, types and stable values, printing a readable diff.
4. Run replay against Laravel (php artisan serve + MySQL) — it must pass 100 %. If MySQL is not installed in the container, install MariaDB (apt) for this session and document the commands in api/contract/README.md.
5. api/contract/README.md: record, replay, what "pass" means, how to add a fixture.
6. Initialise docs/migration/LOG.md.
```

## 2 — Phase 1: Go esasy gurluşy · Fable 5.1

```
Session 2 of the Go + Next.js migration: PROMPT-GO-NEXT.md Phase 1 (Go skeleton).

Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.

Deliver everything listed under Phase 1 in PROMPT-GO-NEXT.md, with special care for:
- Sanctum token compatibility: unit tests that create tokens through Laravel (php artisan tinker or a fixture) and authenticate them in Go; last_used_at update; abilities ignored like today.
- bcrypt $2y$ admin hashes verified in Go (test with the seeded admin).
- Laravel-identical error and 422 bodies, throttle limits per route, CORS, locale handling.
- docker-compose (mysql, redis, api, laravel php-fpm, nginx) where nginx sends /api/healthz to Go and everything else to Laravel; a Makefile with `make dev`, `make test`, `make contract`.
- Add Go/Next.js commands to CLAUDE.md and a `go-builder` agent in .claude/agents (model: opus) describing the Go conventions you chose (package layout, sqlc, handler/service split, error helper, test helpers), so later sessions follow them.
```

## 3 — Phase 2: Katalog · Opus 5.5

```
Session 3 of the Go + Next.js migration: PROMPT-GO-NEXT.md Phase 2 (read-only catalogue).

Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.

Port in parallel builders (one module each, disjoint packages): categories+subcategories, brands+compositions, regions (incl. descendants), shops (index/show + rating summary), products (search with all filters/sorts, by category, show, rating summary), reviews index, banners, home feed (sections, cache + invalidation hooks), payment methods. Guest browsing behaviour must match APP_API_GUEST_BROWSING. When a module's contract fixtures all pass, add its paths to the nginx Go route list. Also expose these in the Go OpenAPI spec.
```

## 4 — Phase 3: OTP giriş we profil · Opus 5.5

```
Session 4 of the Go + Next.js migration: PROMPT-GO-NEXT.md Phase 3 (auth and profile). Add the mobile repo Esca6585dev/sowgatly-app-react-native to this session for an end-to-end check.

Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.

Port: otp/generate, login, register (codes, expiry, OTP_DEBUG_CODE, rate limits, TurkmenistanPhoneNumber rule), logout, device tokens, users/me GET/PUT/POST (multipart + base64 avatar via the same storage paths, birth_date), DELETE users/me/image, me/addresses CRUD. SMS goes through an asynq queue: SMPP (TmCell) primary, Twilio fallback, log-only when unconfigured; unit-test the SMS layer with a fake. Then point the Expo app at the Go API locally (EXPO_PUBLIC_API_URL) and verify login → profile edit → address CRUD works with `npx expo export --platform web` plus a short Playwright run on the web build.
```

## 5 — Phase 4: Sebet, sargyt, synlar · Opus 5.5

```
Session 5 of the Go + Next.js migration: PROMPT-GO-NEXT.md Phase 4 (shopping).

Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.

Port: cart (may hold several shops), favorites + favorite collections, waitlist and the back-in-stock notification trigger on product writes, checkout POST /orders (one order per shop, response keeps `order` and adds `orders`; fulfillment, per-shop delivery fee, payment method/bank, totals, stock decrement — one DB transaction, concurrency-safe with SELECT … FOR UPDATE), orders list/search/show/cancel with restock, reviews create (three criteria, order link, buyer-only). Add Go tests for race conditions on stock and for every Order::TRANSITIONS edge.
```

## 6 — Phase 5: Dükan eýeleri, çatlar, bildirişler · Opus 5.5

```
Session 6 of the Go + Next.js migration: PROMPT-GO-NEXT.md Phase 5 (shop owners, chats, notifications, push).

Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.

Port: owner shop CRUD (one shop per user, image upload, status), owner product CRUD, shop orders + status changes, customer and shop chats (threads, messages, unread counters, read, throttle, HTML stripping), in-app notifications with localized title/body and unread count, FCM HTTP v1 push through the queue (skip silently when unconfigured), shop applications. Finish with 100 % contract pass for /api/*, switch nginx so ALL /api/* goes to Go, and run the full Laravel API test suite against the Go server as a final check. Laravel now serves only the admin.
```

## 7 — Phase 6: Admin API · Opus 5.5

```
Session 7 of the Go + Next.js migration: PROMPT-GO-NEXT.md Phase 6 (admin API in Go).

Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.

Build /admin-api/* JSON endpoints covering every section of the current Laravel admin (read the controllers in app/Http/Controllers/AdminControllers and tests/Feature/Admin for the exact behaviour, validation and business rules: order transitions, shop status, one shop per owner, category hierarchy rules, self-delete protection for admins, CSV export with BOM). Admin auth: bcrypt against `admins`, httpOnly Secure SameSite=Lax session cookie, CSRF for writes, 10/min login limit. RBAC from the Spatie tables (guard admin). Port tests/Feature/Admin to Go tests. Write docs/migration/ADMIN-API.md listing every endpoint with request/response examples for the Next.js session.
```

## 8 — Phase 7a: Next.js admin panel · Opus 5.5

```
Session 8 of the Go + Next.js migration: PROMPT-GO-NEXT.md Phase 7 step 1 (Next.js admin panel).

Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.

Create web/ (Next.js App Router, TypeScript, Tailwind with the tokens from public/admin/admin.css, next-intl tm/ru/en from resources/lang/*.json). First build the shell yourself — layout, collapsible remembered sidebar, light/dark (OS default, remembered, no flash), mobile drawer, top bar, toasts, confirm dialog, table + toolbar + pagination, form fields, file upload with previews, status pills, login page — matching docs/admin-design/*.png and the live Laravel admin. Then give each section to a builder in parallel (dashboard; orders+chats+shop applications+messages; products+carts; shops+addresses; categories+attributes+banners+regions; users+admins+roles+permissions), all calling /admin-api via server-side fetch with the session cookie. Playwright tests: login, one CRUD per section, theme + sidebar persistence, 390px layout. Screenshot every page light/dark and compare with the Laravel admin before switching nginx /admin to Next.js (keep Laravel admin at a hidden path for two weeks).
```

## 9 — Phase 7b: Next.js dükan saýty · Opus 5.5

```
Session 9 of the Go + Next.js migration: PROMPT-GO-NEXT.md Phase 7 step 2 (storefront). Add the mobile repo for reference of screens and texts.

Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.

Build the Flowwow-style storefront specified in PROMPT.md Phase 1 inside web/app/(store)/[locale] using the public /api: city selector (remembered), home (banners, categories, sections from /api/home), catalogue with filters and shareable URLs, search, product page, shop page, favorites, cart, checkout (delivery/pickup, fee, payment method/bank, scheduled time), orders with status timeline and cancel, account (profile, addresses, notifications, waitlist, chats), OTP login (httpOnly cookie holding the API token), shop application form, static pages. SSR + metadata + Open Graph + sitemap.xml + robots.txt, gift-box branding, light/dark. Lighthouse ≥ 90 for performance/SEO/accessibility on home and product pages (report the numbers). Playwright: browse → cart → checkout → order visible in account.
```

## 10 — Phase 8: Deploy we Laravel-i aýyrmak · Fable 5.1

```
Session 10 of the Go + Next.js migration: PROMPT-GO-NEXT.md Phase 8 (cut-over and removing Laravel).

Common rules for this session:
- Read CLAUDE.md, PROMPT-GO-NEXT.md (hard rules, stack, your Phase) and docs/migration/SESSIONS.md first.
- Check the "Ýagdaý" table and the git log: if part of your Phase is already done, continue from there; never redo finished work.
- Laravel must keep working for everything not yet moved. Never change existing /api JSON shapes or drop/alter existing tables.
- Use sub-agents as CLAUDE.md describes (scout for lookups, builder for spec'd modules in parallel; one module per builder, disjoint files).
- Before every push: go test ./..., golangci-lint run, the contract replay for every ported endpoint, and (when web/ changed) next lint + next build. Laravel tests must still pass: php artisan test.
- Commit per module with an English message; push to main.
- At the end: update the "Ýagdaý" row of your session in docs/migration/SESSIONS.md (✅ or 🔄 with what is left), add a short dated entry to docs/migration/LOG.md (what was ported, contract pass rate n/total, open issues), commit and push. Then report the same in Turkmen to the user.

First confirm in docs/migration/LOG.md that sessions 1–9 are ✅ and that production has run fully on Go/Next.js for at least one week; if not, stop and report what is missing. Then: finish deploy/ (docker-compose with mysql, redis, api, worker, web, nginx; volumes for MySQL and uploads; health checks; systemd alternative; .env.example per service), write docs/migration/DEPLOY.md (step-by-step server setup, backup and rollback in Turkmen), delete the Laravel application (PHP code, composer files, Blade, Vite, phpunit) in one commit, optionally move api/ and web/ to the root, and update README.md, CLAUDE.md and .claude/agents/* for the Go + Next.js workflow. Run all Go tests, contract replays and Playwright suites one last time and report.
```
