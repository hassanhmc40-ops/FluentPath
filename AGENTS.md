# AGENTS.md — FluentPath (English Mentor AI)

Project-wide instructions for every agent working in this repo. The full reference is `AGENT.md` — read it for the complete spec (SRS v3.0, MCD/MLD, business rules, deliverables).

## Stack

- Laravel 13, PHP 8.3, MySQL 8 (local dev via XAMPP; tests run on sqlite `:memory:`)
- Pest for tests (`vendor/bin/pest`), Laravel Pint for formatting (`vendor/bin/pint`)
- Auth: Laravel Sanctum; Async AI: Jobs & Queues (database driver); AI provider: Groq (always faked in tests with `Queue::fake()` — never real API calls)
- Routes live in `routes/web.php`; controllers under `app/Http/Controllers/Web/` (admin under `Web/Admin/`); views under `resources/views/`

## Non-negotiable rules

- **NEVER commit, push, or merge code without the user explicitly asking.**
- Before finishing any task: run `vendor/bin/pint` (must be clean) and `vendor/bin/pest` (all tests must pass). Fix any failures before reporting done.

## Design language (current UI redesign)

Match `docs/English Mentor AI.html`:

- Sidebar: dark green `#17211E`; content background: cream `#F5F1EB`; accent green `#29C39F`
- Font: Bricolage Grotesque (loaded via Google Fonts in the layout)
- Numbered nav items grouped by section — students: LEARN / PRACTICE / ACCOUNT; admins: PLATFORM / CATALOG / ACCOUNT
- White cards, rounded corners, level badges (dark bg + accent letter)

## Conventions

- Controllers: thin, return typed views; pass data with named arrays or `compact()`
- Enums: status columns return enum instances — use `->value` when echoing
- `computeSkillTrend` must not use the `latestAnalyzedFor` scope
- No Reading Comprehension anywhere (SRS v3.0 dropped it)
- Keep the WebPagesTest assertions intact: 'Dashboard', 'Placement Test', 'Submit placement test', 'My Roadmap', 'Lessons', 'Writing Practice', 'Notifications', and the quiz title on `/quizzes/{quiz}`

## Scope notes

- MVP has no Teacher role; Admin authors all lessons/quizzes manually; AI only selects existing content
- AI results are validated against strict JSON schemas before storing; failed calls can be retried (rate-limited)
