# English Mentor AI — FluentPath

An AI-powered English learning coach for self-taught learners targeting CEFR A1 to C1. It diagnoses a learner's real level with a holistic placement test, builds a personalized 4-week roadmap from an existing lesson catalog, corrects free-form writing with explained AI feedback, and adapts continuously as the learner progresses.

---

## The Learning Loop

The app follows an **adaptive learning loop**, not a linear pipeline:

1. **Register** — create a student account.
2. **Placement Test** — answer Grammar, Vocabulary, Reading, and Writing questions in one submission.
3. **AI Evaluation** — the AI holistically assesses the submission, returning a CEFR level with per-skill scores, strengths, weaknesses, and reasoning.
4. **Personalized Roadmap** — the AI generates a 4-week plan using only existing catalog content.
5. **Lessons & Quizzes** — follow the roadmap: read lessons, take quizzes.
6. **Writing Practice** — submit free-form writing.
7. **AI Feedback** — the AI corrects the text with structured, explained feedback (grammar, vocabulary, fluency, specific mistakes).
8. **Dashboard & Recommendations** — progress updates, learning streak, and refreshed recommendations after every activity.

Then **loop back** into lessons, quizzes, and writing. Every completed activity feeds back into the recommendation engine, keeping the roadmap aligned with the learner's ongoing progress.

---

## Core Features

- **Holistic AI Placement Test** — Grammar, Vocabulary, Reading, and Writing questions evaluated jointly (not scored independently).
- **AI-Generated 4-Week Roadmap** — personalized plan; the AI only selects *existing* catalog content, never invents new material.
- **Admin-Managed Catalog** — 60 lessons and 60 quizzes covering CEFR A1 through C1, authored and maintained by administrators.
- **Writing Correction** — free-form submissions corrected asynchronously with structured feedback: corrected text, per-skill comments, individual mistake analysis, and next-topic recommendations.
- **Recommendation Engine** — refreshes the next lesson, grammar topic, vocabulary topic, and writing exercise after every activity.
- **Progress Dashboard** — CEFR level, completed lessons vs. total, writing score trend, learning streak, and next recommended action.
- **In-App Notifications** — alerts when AI results are ready (placement test analyzed, roadmap generated, writing corrected).
- **Async AI via Jobs & Queues** — every AI call runs on a Laravel Job; the user gets a 202 Accepted immediately, never a blocking request.

---

## AI & Asynchrony

Every AI-powered feature (placement evaluation, roadmap generation, writing correction) follows the same pattern:

- A **Laravel Job** is dispatched to the **database queue driver**.
- The user receives an immediate **202 Accepted** response — the UI is never blocked.
- The job calls the **Groq** provider via `laravel/ai`.
- The response is validated against a **strict JSON schema**; invalid responses are rejected and logged.
- Results are stored through Eloquent JSON casts and the user is notified.
- AI-triggering routes are **rate-limited** (`throttle:ai` middleware).
- In tests, the queue is always faked with `Queue::fake()` — real API calls are never made.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 (`^13.8`) |
| Language | PHP 8.3 |
| Database | MySQL 8 (local dev via XAMPP) |
| Auth | Laravel Sanctum 4 |
| AI Provider | Groq (via `laravel/ai`) |
| Queue Driver | Database |
| Testing | Pest 4 (`^4.7`) |
| Code Style | Laravel Pint (`^1.29`) |
| Frontend | Blade templates, custom CSS |
| Design Font | Bricolage Grotesque (Google Fonts) |

---

## Roles

| Role | Description |
|---|---|
| **Student** | Registers, takes placement test, follows roadmap, completes lessons/quizzes, submits writing, tracks progress. |
| **Admin** | Creates and manages the full content catalog (lessons, quizzes, placement questions), monitors platform usage. Admins never manually edit student roadmaps. |

---

## Local Setup (Windows / XAMPP)

### Prerequisites

- PHP 8.3+ (XAMPP)
- MySQL 8 (XAMPP)
- Composer
- Node.js & npm

### Steps

```bash
# 1. Clone the repository
git clone <repo-url> FluentPath
cd FluentPath

# 2. Install PHP dependencies
composer install

# 3. Create environment file and application key
copy .env.example .env
php artisan key:generate

# 4. Create the MySQL database
#    Open phpMyAdmin or mysql CLI and run:
#    CREATE DATABASE fluentpath;

# 5. Configure .env for MySQL and queue
#    Set these values in your .env:
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=fluentpath
#    DB_USERNAME=root
#    DB_PASSWORD=
#    QUEUE_CONNECTION=database

# 6. Run migrations and seed the database
#    Seeds 60 lessons, 60 quizzes, 300 quiz questions,
#    100 placement questions, and 6 demo accounts.
php artisan migrate --seed

# 7. Start the development server
php artisan serve

# 8. Start the queue worker (required for AI features)
#    Results (placement, roadmap, writing) only arrive while this runs.
#    Keep it in its own terminal; jobs are retried up to 3 times with backoff
#    and time out after 300s (AI calls use a 120s HTTP timeout).
php artisan queue:work

# 9. (Optional) Start the task scheduler
#     For daily maintenance tasks (recommendation refresh, inactivity reminders):
php artisan schedule:work
#     Or add a cron job: * * * * * php artisan schedule:run
```

---

## Demo Accounts

All accounts use the password `password`.

| Role | Name | Email | Level |
|---|---|---|---|
| Admin | Admin | `admin@fluentpath.com` | — |
| Student | Sara Benali | `sara@fluentpath.com` | A1 |
| Student | Yassine El Amrani | `yassine@fluentpath.com` | A2 |
| Student | Lina Haddad | `lina@fluentpath.com` | B1 |
| Student | Omar Tazi | `omar@fluentpath.com` | B2 |
| Student | Nadia Berrada | `nadia@fluentpath.com` | C1 |

Each demo student is pre-seeded with a completed placement test, a generated roadmap, quiz attempts, writing submissions, and notifications so you can explore the dashboard immediately.

---

## Running Tests & Formatting

```bash
# Run the test suite (uses SQLite in-memory)
vendor\bin\pest

# Check code formatting
vendor\bin\pint
```

---

## API Documentation

An API is available alongside the web interface (auth via Laravel Sanctum tokens). Interactive documentation is generated with [Scribe](https://scribe.knuckles.wtf) (already installed as a dev dependency):

```bash
php artisan scribe:generate
```

The docs are served by the app itself:

- **Web UI** — `http://localhost:8000/docs`
- **Postman collection** — `http://localhost:8000/docs.postman`
- **OpenAPI spec** — `http://localhost:8000/docs.openapi`

Every endpoint is annotated in the API controllers (`app/Http/Controllers/Api/`) and documented with example requests, `@bodyParam`/`@queryParam`/`@urlParam` definitions, and sample responses. Most endpoints require a Sanctum bearer token (`Authorization: Bearer <token>`), obtained from `POST /api/register` or `POST /api/login`.

---

## Documentation & Deliverables

| Document | Path | Description |
|---|---|---|
| Full Spec (SRS v3.0) | [`AGENT.md`](AGENT.md) | Complete software requirements specification, MCD/MLD, business rules, and deliverables. |
| Project Context | [`Projectcontext.md`](Projectcontext.md) | Exhaustive reference: architecture, data model, decisions, and feature details. |
| MCD Diagram | [`docs/MCD.png`](docs/MCD.png) | Conceptual data model. |
| MLD Diagram | [`docs/MLD.png`](docs/MLD.png) | Logical data model. |
| System Architecture | [`docs/SystemArchitecture.png`](docs/SystemArchitecture.png) | High-level architecture diagram. |
| Design Mockup | [`docs/English Mentor AI.html`](docs/English%20Mentor%20AI.html) | Interactive UI design reference (dark green sidebar, cream content area, Bricolage Grotesque). |
