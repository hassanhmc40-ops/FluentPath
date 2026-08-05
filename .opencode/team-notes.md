# Team Blackboard

Shared thinking space for the 3-agent team: **build** (orchestrator), **mimo**, **big-pickle**.

Rules:
- Read this file before starting any task.
- Append your updates under the matching section — never rewrite others' entries.
- Keep entries short: one or two lines each.

## Current Goal

FluentPath (English Mentor AI) — capstone due August 7, 2026. **REBUILD ALL BLADE VIEWS FROM SCRATCH** to match `docs/English Mentor AI.html` EXACTLY (user: "delete all and build from scratch forget the current"). Design = SRS v2.0 mockup: sidebar `#17211E`, content bg `#F6F2EC`, accent `#29C39F`, orange `#E0603B`, Bricolage Grotesque + IBM Plex Sans/Mono, inline styles + keyframes (rise/fadein/slideL/slideR/orbit/pulse/dash/grow/shimmer/float/blink/drift). Design reference (unescaped HTML): `.opencode/design-reference.html`; global CSS + fonts: `.opencode/design-css.txt`; mock state data: `.opencode/design-state.txt`.

## Design System (from mockup — match EXACTLY)

- **Palette:** sidebar bg `#17211E`; page bg `#F6F2EC`; accent `#29C39F`; deep green `#0E6B5C`; orange `#E0603B`; cream card `#FFFDFA`/`#FFFEFC`; borders `#E0D8CC`/`#E5DDD2`; text `#17211E`/`#55605A`/`#8A8378`/`#B9C5C0`; on-dark `#EFEAE2`/`#A6B4AE`/`#7E9089`; success `#E3F2EE`/`#0A5347`; danger `#FBE7DF`/`#A73E1E`/`#A73E1E`.
- **Fonts:** Bricolage Grotesque (headings/logo), IBM Plex Sans (body), IBM Plex Mono (labels/crumbs).
- **Sidebar 252px dark:** logo 38px rounded-12 gradient `#29C39F→#0E6B5C` "E", "English Mentor AI" Bricolage 17/700; nav groups LEARN / PRACTICE / ACCOUNT (students) and PLATFORM / CATALOG / ACCOUNT (admins); items numbered `01..10`, active = bg `#29C39F` + text `#06231D`, inactive `#B9C5C0`; streak widget; "Switch to admin/student" + logout; user row at bottom.
- **Header:** 84px, crumb (mono 11-12px `#8A8378` uppercase) + title Bricolage 28/800 `#17211E`; queue pill ("1 AI job running" style) + bell button w/ dropdown notification panel + "View all".
- **Keyframes (global):** rise, fadein, slideL, slideR, orbit, pulse, dash, grow, shimmer, float, blink, drift (drift for auth blobs: radial-gradient `rgba(41,195,159,.32)` & `rgba(224,96,59,.22)`).
- **Cards:** white `#FFFDFA`, radius 16-20px, border `#E5DDD2`/shadow subtle; level badges dark bg + accent letter; skeleton bars `shimmer`.
- Auth: grid `1.05fr 1fr`; left dark panel (logo, headline 46/800/-1.6px "The coach that finds out what you actually know.", sub `#A6B4AE` 15px/1.65, feature bullets, stats row mono 11px, footer "Simplon Maghreb × JobinTech · SRS v2.0"); right = tabs Sign in/Create account, fields w/ focus ring `#0E6B5C`, CTA dark `#17211E` rounded 12, demo buttons (student/admin).

## Screens (sc-if) & Blade mapping

Auth (login/register tabs) → `auth/login`, `auth/register` (or one tabbed page); Onboarding (3-step: goal/time/struggle, dots, option cards) → new `onboarding`; inApp shell → `layouts/app`; isDash → `dashboard`; isTest (intro w/ 4 part cards, question flow w/ parts progress, writing part, analyzing, done w/ CEFR + per-skill + strengths/weaknesses) → `placement-test`; isRoadmap (4 week cards w/ status Done/Active/Locked) → `roadmap`; isLessons (filter pills + cards w/ level badge + state + btn) → `lessons/index`; isLessonDetail (sections w/ examples + attached exercises + complete toggle) → NEW `lessons/show` (add route + controller method); isExercise (running w/ options A-D + why-feedback, done w/ score) → `quizzes/show`; isWriting (idle placeholder / loading skeleton / failed retry / done w/ mistakes + feedback + next topics) → `writing/index`; isSubs (table w/ chips) → `writing/submissions`; isProgress (timeline w/ dots) → `progress/index`; isNotifs (list + mark all read) → `notifications/index`; isSettings (profile fields + toggles + danger zone) → `settings/index`; admin: overview (stats + jobs queue monitor + top lessons) → `admin/overview`; lessons (catalog table + inline new-lesson form) → `admin/lessons/index`; exercises (cards) → `admin/quizzes/index`; students (table w/ sparklines) → `admin/students`.

## Decisions

- **USER OVERRIDE (Aug 4):** Match the design EXACTLY even though it's SRS v2.0 — ADD Reading back: `Skill::Reading = 'reading'`, reading placement questions, reading lessons, 4-part test UI (Grammar/Vocab/Reading/Writing), Reading filter/lessons/exercises. This supersedes the old "no Reading (v3.0)" rule.
- **USER OVERRIDE (Aug 4):** Implement the 3-step onboarding screen after registration (goal, weekly hours, struggles) → store answers on user (new columns), then go to placement test.
- NEVER commit, push, or merge code without the user explicitly asking.
- Demo credentials: `admin@fluentpath.com` / `password`; students via `UserFactory` (password `password`).
- Before finishing: `vendor/bin/pint` clean + `vendor/bin/pest` ALL green (tests run on sqlite :memory:).
- WebPagesTest strings MUST stay on pages: 'Dashboard', 'Placement Test', 'Submit placement test', 'My Roadmap', 'Lessons', 'Writing Practice', 'Notifications', quiz title on /quizzes/{quiz}. NOTE: mockup titles say "Placement test"/"Roadmap"/"Writing studio" — use the TEST strings in h1/page titles where required (keep design styling).
- Admin nav per design: PLATFORM (01 Overview → /admin, 02 Students → /admin/students), CATALOG (03 Lessons → /admin/lessons, 04 Exercises → /admin/quizzes), ACCOUNT (05 Notifications, 06 Settings). Admin dashboard route for admins currently returns plain view — keep.
- Backend unchanged where possible; views are the deliverable. New: Reading in Skill enum + factories + seeders; onboarding columns/migration/controller/routes; `lessons.show` route.

## Progress

- Round 1 (build): old restyle (pre-rebuild) — 130/130 tests green, Pint clean. Being REPLACED by the from-scratch rebuild.
- Round 2 (build): design fully extracted to `.opencode/design-reference.html` + `design-css.txt` + `design-state.txt`; all screens/mock state analyzed; backend groundwork done (Skill::Reading, onboarding migration+controller+routes, seeder updates, lesson show route) — see commit-less workspace state.
- Round 2 (build): auth/login + auth/register + onboarding + layouts/app rebuilt from scratch (split layout, drift blobs, tab pills, demo buttons; sidebar 252px w/ numbered nav groups + streak widget + switch-view + bell dropdown `#bell-panel`; header w/ crumb + queue pill; flashes). Root `/` now redirects (guest → /login, auth → /dashboard); RegisterController → /onboarding. ExampleTest rewritten to assert those redirects (was expecting 200 on `/`). Pest 131/131 green.
- Round 2 (build): verified all 8 big-pickle student views on disk (quizzes/show one-question-at-a-time player w/ A-D + feedback + hidden inputs POST; writing/index 4 states incl. failed-retry re-queueing same job w/ prompt+original_text hidden inputs; settings toggles client-side; dashboard/lessons-show/settings reviewed & match design).
- Round 2 (mimo): 4 student views — see Mimo's Screen Progress below.

- Round 2 (big-pickle): rebuilt 4 admin index views per design ref — `admin/overview` (4 stat cards: students/lessons/exercises/pending-jobs w/ combined pending_writing+pending_placement, queue monitor card w/ idle state, Most completed lessons w/ slideL bars scaled to max completions), `admin/lessons/index` (count row + "+ New lesson" JS toggle form POST → admin.lessons.store w/ title/skill/level selects from Skill::cases()/CefrLevel::cases(); table grid 2fr/1fr/90px/100px/170px w/ skill chips + level mono + quizzes_count + Edit/DELETE), `admin/quizzes/index` (auto-fill card grid, type derived from linked lesson skill or 'Exercise', items chip = quiz_questions_count, Edit + DELETE, "+ New exercise" → create), `admin/students` (table w/ avatar+name+email, level chip, lessons done, last activity 'M j, Y', deterministic sparkline bars). Deviations: dropped design's Content textarea in new-lesson form (Lesson model has no content field; StoreLessonRequest only title/skill/level); delete buttons labeled "Delete" not design's "Archive" (route is destroy); design's "Completions" table column replaced w/ exercises count per data contract; skill = light chip not plain muted text. Verified: pint clean, view:cache ok, pest 131/131 green, browser smoke-tested all 4 pages logged in as admin (form toggle + lesson create + success toast verified, test row cleaned up).

- **ROUND 2 COMPLETE — ALL VIEWS REBUILT.** Final gate: `vendor/bin/pint` clean on all rebuilt views; `vendor/bin/pest` 131/131 green (606 assertions), incl. ExampleTest now asserting `/` redirects. Remaining for Round 3: full visual QA pass (screenshots vs mockup), then report to user.
- **Round 3 (build) — Chrome QA screenshot pass** (26 shots in `.opencode/qa-screenshots/`): all admin + student pages + auth/onboarding flows render with real data, quiz player answered end-to-end (POST /quizzes/1/attempt → success state), placement test submitted (queued, processing state shown, queue pill updates). BUGS FOUND & FIXED during pass:
  1. **onboarding.blade.php**: `picked` map keyed by step index but hidden inputs by name → goal/hours/struggle never submitted (validation error). Fixed: key by `stepNames[index]`; also step title/sub now update per step (`data-title`/`data-sub` + `onb-title`/`onb-sub` elements), `$steps` array moved before first use (was "Undefined variable").
  2. **OnboardingController::store**: saved `goal` key but DB column is `onboarding_goal` → goal silently dropped. Fixed: explicit map (`onboarding_goal => input('goal')`).
  3. **Dev DB**: migration `2026_08_04_000001_add_onboarding_to_users_table` was Pending on local sqlite (dev env is sqlite, not XAMPP MySQL) → ran `php artisan migrate`. Reading placement questions + lessons were missing from local DB (seeded pre-Reading) → added 4 + 5 via tinker (idempotent script pattern in temp dir).
  4. Simulated generated roadmap for user 2 (status generated + 4 weeks × 4 lessons) to screenshot week-cards state — real data for demos now.
  - Added `tests/Feature/OnboardingTest.php` (4 tests: page renders, store persists all 3 fields, invalid values rejected, guest redirect). NEW GATE: **pest 135/135, 623 assertions, pint clean**.
  - QA user created: `qa.tester.0804@example.com` / password (has pending placement test — the queued job stays pending, no worker, NO real Groq calls made).

## Mimo's Screen Progress

- **dashboard.blade.php** ✅ — Dark hero card w/ level letter + radial blob + progress ring, 4 stat cards, writing score bar chart, skill balance bars, next-up card, admin placeholder. Matches design isDash.
- **placement-test.blade.php** ✅ — Intro hero card (4 part cards), server-side form grouping questions by skill (grammar/vocab/reading/writing), open text inputs per Q, analyzing spinner state, done state (level card + per-skill scores + strengths/weaknesses pills + roadmap CTA). Matches design isTest.
- **roadmap.blade.php** ✅ — 4 week cards (Done/Active/Locked), progress bars, lesson items with kind tags, skeleton processing state, empty state CTA. Matches design isRoadmap.
- **lessons/index.blade.php** ✅ — Filter pills (All/Grammar/Vocabulary/Reading/Writing) with inline JS toggle, lesson cards grid (skill label + level badge + title + desc + state + button). Matches design isLessons.
- All 4 files: Pint clean ✅. Test assertions verified: 'Dashboard', 'Placement Test', 'Submit placement test', 'My Roadmap', 'Week 1', 'Lessons' all present.

## Open Questions

- Whether to retrofit the MCD with a WEEK entity or explain the MLD refinement during the defense (see AGENT.md §17) — NOT blocking the redesign.
- PR for GitHub Actions CI (`ci/github-actions` branch) — user hasn't granted permission.
