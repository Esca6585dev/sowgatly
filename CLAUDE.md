# CLAUDE.md — Sowgatly backend (Laravel 10)

Read `README.md` first. Open work is listed in `PROMPT.md` (website, cleanup, APK),
`PROMPT-API.md` (API for the Flutter app) and `PROMPT-CLIENT.md` (customer website and
account panel in four languages, prototype in `docs/client-design/`); each starts with a
status note.

## Model routing (save Fable / Opus limits)

Start sessions on **Opus 5.5** (`/model claude-opus-5-5`). It handles almost all of this
project. Use the agents in `.claude/agents/` to push work to the cheapest model that can
do it:

| Work | Who |
|---|---|
| Find files, list routes/migrations, summarise a controller, check what exists | `scout` (Haiku 4.5) |
| Implement a spec'd endpoint, migration, Blade page, tests, docs, Swagger | `builder` (Opus 5.5) |
| Design a new subsystem, a bug that resisted two attempts, a security review | `architect` (Fable 5.1) |

Ask the user to switch the main session to Fable only for the third row.

## Delegation

- Do it yourself when it touches 1–3 files you already know. Delegating small edits
  costs more tokens than it saves.
- One sub-agent per independent task; launch independent ones in parallel.
- Give a sub-agent exact file paths, the acceptance check (test or command) and what to
  return. Ask for a short summary, not file dumps.
- Do not re-read files a sub-agent already summarised unless you are going to edit them.

## Project rules

- Never change the JSON shape of an existing `/api/*` endpoint: the React Native and
  Flutter apps both use them. Add fields, add optional request fields, add routes.
- Migrations are additive (nullable or with defaults).
- New controllers use `Api\Concerns\RespondsWithJson`; every new endpoint gets `@OA`
  annotations and a feature test.
- Tests: `php artisan test`. `phpunit.xml` uses an in-memory SQLite database, so no
  MySQL is needed (export `DB_CONNECTION=mysql` to run them against MySQL instead).
- After API changes: `php artisan l5-swagger:generate` and commit the JSON.
- Never commit `.env`, `vendor/`, `node_modules/`, keystores or keys.
- The owner merges straight to `main`; commit per feature with an English message.
