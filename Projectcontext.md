# Project Context — English Mentor AI ("FluentPath")

This document is the exhaustive reference for the project: history, decisions, full data model, full architecture, and everything discussed across SRS drafts, MCD/MLD revisions, and the architecture diagram. It is meant to be read start to finish by someone with zero prior context.

---

## 0. Meta

| | |
|---|---|
| Project name | English Mentor AI |
| Internal/diagram codename | FluentPath |
| Type | Capstone ("Fil Rouge") project |
| Program | Simplon Maghreb × JobinTech Bootcamp, July 2026 cohort |
| Author | Hassan |
| Audience of the app | Self-taught English learners, CEFR A1 to C1 |
| Submission deadline | **August 7, 2026** |
| Oral defenses (soutenances) | Start **August 10, 2026** |
| Current spec version | **SRS v3.0** (supersedes an earlier v2.0 draft dated July 15, 2026 — see §12 for what changed) |

---

## 1. The Problem

Most existing English-learning platforms give every learner the same fixed curriculum, regardless of their actual level or weaknesses, and reduce "correction" to closed-choice multiple-choice quizzes. As a result, learners:

- Rarely know their real English level.
- Don't know what to prioritize studying.
- Keep repeating the same mistakes because they never get an explanation of *why* something is wrong — only whether their multiple-choice answer was right or wrong.

**English Mentor AI's answer:** a web app that diagnoses a learner's real level holistically, builds a personalized 4-week roadmap, recommends targeted existing lessons/quizzes, and corrects free-form written work with detailed, explained AI feedback — continuously adapting as the learner progresses.

### Why AI is the core, not a feature bolted on
The AI is what performs the three most valuable functions in the product:
1. Evaluates the placement test holistically (not just grading multiple-choice).
2. Generates the personalized roadmap.
3. Reviews and explains every piece of free-form writing.

If you removed the AI, there would be nothing left that justifies the app's existence — it would just be a static lesson library.

---

## 2. Objectives

| Code | Objective |
|---|---|
| O1 | Assess the learner's real English level through a holistic placement test |
| O2 | Generate a personalized 4-week roadmap from that assessment |
| O3 | Recommend existing lessons and quizzes based on the learner's evolving profile |
| O4 | Correct written submissions with detailed, explained feedback |
| O5 | Track and visualize progress in a single dashboard |
| O6 | Run every AI call asynchronously (Jobs/Queues) and never block the UI |
| O7 | Keep every AI output structured, validated, and safely stored |

---

## 3. Scope

### 3.1 In scope (MVP)
- Auth & profile management for two roles (Student, Admin) via Laravel Sanctum.
- Holistic AI placement test — **Grammar, Vocabulary, and Writing only.**
- AI-generated 4-week personalized roadmap.
- Lesson catalog, fully Admin-managed, with per-student completion tracking.
- Quizzes tied to lessons (multiple choice), with attempt tracking (multiple attempts allowed).
- Writing module with detailed, structured AI correction.
- Recommendation engine that re-evaluates and refreshes recommendations after every learner activity (lesson completed, quiz attempted, writing submitted).
- Progress dashboard.
- In-app notifications.
- A scheduled task (Laravel Scheduler) for daily recommendation refresh and/or inactivity reminders.

### 3.2 Explicitly out of scope for the MVP
- **Reading Comprehension** as a placement-test / core skill. This was in an earlier draft (SRS v2.0) but was **deliberately dropped**; the current placement test and skill model cover only Grammar, Vocabulary, and Writing.
- Pronunciation assessment (audio analysis).
- Real-time AI conversation simulator.
- Native mobile app.
- Payments.
- Social features / leaderboards.
- **Teacher role and class management** — explicitly excluded from the MVP; noted only as a long-term/optional idea, not built.
- **AI-generated lesson or quiz content.** In the MVP, 100% of lessons and quizzes are authored manually by the Admin. The AI never creates educational content — it only selects, orders, and recommends existing content. An "AI Lesson Generator" exists **only as a documented bonus/future-work section** in the SRS; it is not implemented.

---

## 4. Roles & Personas

Only two roles exist in the MVP.

### Student
- Registers and manages their own profile.
- Takes the placement test.
- Receives the AI evaluation and the generated roadmap.
- Follows the roadmap: completes lessons, attempts quizzes, submits writing.
- Reviews AI feedback on each writing submission.
- Tracks progress via the dashboard.
- Receives notifications when async AI results are ready.

### Admin
- Creates and manages the entire content catalog: lessons, quizzes, quiz questions, placement questions.
- Monitors platform usage/engagement.
- **Never** manually creates or edits an individual student's roadmap — roadmap generation stays fully automated/AI-driven. The Admin's role is content curation, not per-student intervention.

---

## 5. User Stories

### As a Student
- I want to register so that I can access my personalized learning space.
- I want to take a placement test so that the AI can determine my English level.
- I want a personalized roadmap so that I know what to study next.
- I want the AI to correct my writing and explain my mistakes so that I can improve.
- I want to track my progress so that I can monitor my improvement over time.
- I want to be notified when new feedback or recommendations are ready so that I don't have to check manually.

### As an Admin
- I want to create and manage lessons and quizzes so that students always have quality content.
- I want to monitor platform usage so that I can assess engagement.

---

## 6. Core Features

| ID | Feature | Description | Priority |
|---|---|---|---|
| F01 | Authentication | Register, login, logout, profile management (Sanctum) | High |
| F02 | Placement test | Grammar, Vocabulary, and Writing questions submitted together | High |
| F03 | AI evaluation | Holistic CEFR assessment with reasoning, strengths, and weaknesses | High |
| F04 | AI roadmap | 4-week personalized learning plan | High |
| F05 | Lessons & quizzes | Admin-managed catalog; students complete lessons and attempt quizzes | High |
| F06 | Writing correction | Detailed AI feedback on free-form text | High |
| F07 | Recommendation engine | Updates next lesson/topics after every activity | High |
| F08 | Dashboard | Progress indicators and history | High |
| F09 | Notifications | Alerts when AI results are ready | Medium |
| F10 | Admin content management | CRUD on lessons, quizzes, and placement questions | Medium |

---

## 7. AI Features — Full Detail

### 7.1 Holistic Placement Test Evaluation
The placement test is **not** a simple auto-graded multiple-choice quiz. It combines Grammar, Vocabulary, and Writing questions, submitted together as one package. The AI reviews the entire submission the way a human teacher would — considering all three skills jointly rather than scoring closed answers independently.

**AI returns:**
- CEFR level (A1–C1)
- Per-skill scores (grammar, vocabulary, writing)
- Strengths and weaknesses (qualitative)
- A short written justification / reasoning for the assessment
- Initial recommendations that feed directly into the roadmap-generation step

### 7.2 4-Week Personalized Roadmap
Generated from the placement-test result. Structured as **4 weeks**, each week containing:
- An objective
- Recommended lessons
- Grammar topics
- Vocabulary topics
- A writing activity
- Suggested quizzes/exercises

**Hard constraint:** every lesson/quiz referenced in the roadmap must already exist in the Admin-managed catalog. The AI never invents content — it only assembles a personalized path out of existing pieces.

If a student retakes the placement test, a **new roadmap can be generated while previous roadmaps are preserved** (historical record, not overwritten).

### 7.3 Lesson/Quiz Catalog & Recommendation Engine
- All lessons and quizzes are created and maintained exclusively by the Admin.
- The AI never creates a lesson or quiz in the MVP; it only selects and orders existing catalog items that match the learner's current profile.
- After **every** completed activity — a lesson finished, a quiz attempted, or a writing piece submitted — the recommendation engine re-evaluates the learner's profile and refreshes: the next recommended lesson, the next grammar topic, the next vocabulary topic, and the next writing exercise.

### 7.4 Writing Correction
Writing submissions are corrected **asynchronously** (queued job, not a blocking HTTP request). The AI returns a structured JSON payload, for example:

```json
{
  "corrected_text": "Yesterday I went to school.",
  "score": 82,
  "grammar_feedback": "Past Simple required after \"yesterday\".",
  "vocabulary_feedback": "Good range; consider more varied connectors.",
  "fluency_feedback": "Sentences are short; try combining ideas.",
  "mistakes": [
    { "original": "go", "correction": "went", "rule": "Past Simple" }
  ],
  "recommendations": ["Review Past Simple", "Practice linking words"],
  "next_topics": ["Past Simple", "Irregular verbs"]
}
```

This structure is stored via Eloquent JSON casts on the `writing_submissions` table (see §10).

### 7.5 AI Workflow & Guardrails (applies to all three AI features above)
- **Asynchronous by design:** every AI call (placement evaluation, roadmap generation, writing correction) is dispatched through a Laravel Job. The user gets an immediate `202 Accepted` response — never a blocking call that waits on the AI provider.
- **Strict schema validation:** responses must match a defined JSON schema (via structured output support in the AI SDK). Any response that doesn't conform is **rejected and logged**, not silently accepted.
- **Storage discipline:** AI results are stored through Eloquent Casts (JSON casts for structured fields like strengths/weaknesses/mistakes/recommendations). API keys for the AI provider (Groq) live only in `.env` and are never committed.
- **AI cannot unilaterally act:** the AI can only recommend lessons/quizzes that already exist in the database, and it never sets a final grade or unlocks content on its own initiative — the **application layer validates and authorizes** before anything is persisted or unlocked.
- **Resilience:** rate limiting applies to every AI-triggering route; if a call fails, the student is allowed to retry (subject to that rate limit).

---

## 8. User Journey — the Adaptive Learning Cycle

```
Register
   → Placement Test
   → AI Evaluation
   → Personalized Roadmap
   → Lessons
   → Quizzes
   → Writing Practice
   → AI Feedback
   → Dashboard Update
   → Personalized Recommendations
   → (loops back into Lessons/Quizzes/Writing)
```

This is explicitly a **loop, not a linear pipeline**: every writing submission or completed lesson/quiz feeds back into the recommendation engine, which keeps the roadmap and "what's next" aligned with the learner's actual, ongoing progress — not just their one-time placement-test result.

---

## 9. Dashboard & Progress Tracking

The dashboard surfaces:
- Current CEFR level and current roadmap week.
- Completed lessons vs. total lessons.
- Writing score history (a trend over time, not just the latest score).
- Grammar and vocabulary improvement indicators.
- Current learning streak (consecutive active days).
- Overall progress percentage and the single next recommended action.

## 9.1 Notifications
A lightweight in-app notification system tells the student when an asynchronous AI result becomes available, or when new recommendations are ready. Trigger events:
- Placement test analyzed.
- Personalized roadmap generated.
- Writing correction completed.
- New recommendations available.

---

## 10. Data Model

### 10.1 Conceptual Model (MCD) — full detail

The MCD is DBMS-independent: no foreign keys, no data types, no technical associative/pivot tables — those are introduced only at the MLD stage.

**USERS** — the central entity; every authenticated person (Student or Admin).
- Attributes: `id`, `name`, `email`, `password`, `role`
- Relationships: can take zero-or-several placement tests; can have zero-or-several roadmaps; can complete zero-or-several lessons; can attempt zero-or-several quizzes; can submit zero-or-several writing submissions; can receive zero-or-several notifications.

**PLACEMENT_TESTS** — a complete English-level assessment taken by a learner; stores the overall result (CEFR level + per-skill scores). A learner may retake the test to get an updated roadmap.
- Attributes: `id`, `submitted_at`, `status`, `cefr_level`, `grammar_score`, `vocabulary_score`, `writing_score`
- Relationships: taken by exactly one user; contains one-or-several placement questions; can generate zero-or-one roadmap.

**PLACEMENT_QUESTIONS** — the question bank used to evaluate learners, created/maintained by the Admin, categorized by skill and level. The same question can be reused across several tests.
- Attributes: `id`, `question`, `skill`, `level`
- Relationships: can appear in zero-or-several placement tests. This is an **N:N relationship** with PLACEMENT_TESTS (resolved via an associative table only in the MLD).

**ROADMAPS** — the personalized learning plan generated by the AI once a placement test has been evaluated. Retaking the test can generate a new roadmap while old ones are preserved.
- Attributes: `id`, `title`, `objective`, `generated_at`
- Relationships: belongs to exactly one user; generated from exactly one placement test; recommends one-or-several lessons (N:N with LESSONS).

**LESSONS** — the educational content, created/maintained by the Admin (grammar, vocabulary, writing, etc.). The AI never creates lessons — it recommends existing ones.
- Attributes: `id`, `title`, `skill`, `level`
- Relationships: can appear in zero-or-several roadmaps (N:N); can be completed by zero-or-several users (N:N with USERS); can contain zero-or-several quizzes (1:N — each quiz belongs to exactly one lesson).

**QUIZZES** — an assessment tied to a lesson, checking whether the learner understood the content. Learners may re-attempt the same quiz.
- Attributes: `id`, `title`, `description`
- Relationships: belongs to exactly one lesson; can be attempted by zero-or-several users (N:N with USERS — individual attempt *records* are introduced only at the MLD stage); contains one-or-several quiz questions.

**QUIZ_QUESTIONS** — the individual questions inside a quiz (text + options + correct answer). Not directly related to a user — the learner interacts with the quiz as a whole, not with individual questions conceptually.
- Attributes: `id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`
- Relationships: belongs to exactly one quiz.

**WRITING_SUBMISSIONS** — the free-form writing exercises submitted by learners; stores the original text and, once processed, the corrected text/score/status. Provides a full history of writing activity.
- Attributes: `id`, `prompt`, `original_text`, `corrected_text`, `score`, `status`
- Relationships: belongs to exactly one user; a user can submit zero-or-several.

**NOTIFICATIONS** — messages sent inside the app about placement-test evaluation completed, roadmap generated, writing correction completed, new recommendations available, or learning reminders.
- Attributes: `id`, `title`, `message`, `is_read`
- Relationships: belongs to exactly one user; a user can receive zero-or-several.

### 10.2 Global Relationship Summary (MCD)

| Relationship | Cardinality | Description |
|---|---|---|
| Users → Placement Tests | 1:N | A user can take multiple placement tests; each test belongs to one user |
| Placement Tests ↔ Placement Questions | N:N | A test contains several questions; a question can be reused across tests |
| Placement Tests → Roadmaps | 1:(0..1) | A test may generate one roadmap; each roadmap comes from exactly one test |
| Users → Roadmaps | 1:N | A user can receive several roadmaps over time |
| Roadmaps ↔ Lessons | N:N | A roadmap recommends several lessons; a lesson can appear in several roadmaps |
| Users ↔ Lessons | N:N | A user completes several lessons; a lesson is completed by several users |
| Lessons → Quizzes | 1:N | A lesson can contain several quizzes; each quiz belongs to one lesson |
| Users ↔ Quizzes | N:N | A user attempts several quizzes; each quiz is attempted by several users |
| Quizzes → Quiz Questions | 1:N | A quiz contains several questions; each question belongs to one quiz |
| Users → Writing Submissions | 1:N | A user can submit several writing exercises |
| Users → Notifications | 1:N | A user can receive several notifications |

### 10.3 Conceptual Many-to-Many Relationships (to be resolved in MLD)

| Entities | Relationship name | Meaning |
|---|---|---|
| Placement Tests ↔ Placement Questions | `contain` | Tests use several questions; questions can be reused |
| Roadmaps ↔ Lessons | `recommend` | Roadmaps recommend lessons that can appear in other roadmaps |
| Users ↔ Lessons | `complete` | Users complete several lessons; lessons are completed by several users |
| Users ↔ Quizzes | `attempt` | Users attempt several quizzes; quizzes are attempted by several users |

### 10.4 MCD Revision History (what changed, in order)
1. Fixed inverted cardinalities that were present in an earlier draft.
2. Added missing entities that weren't originally modeled explicitly (roadmap-progress and lesson-progress concepts).
3. Resolved the PLACEMENT_TEST ↔ PLACEMENT_QUESTION many-to-many properly (via what becomes PLACEMENT_ANSWERS at the MLD stage).
4. Standardized on **plural entity names** across the whole diagram: USERS, PLACEMENT_TESTS, PLACEMENT_QUESTIONS, ROADMAPS, LESSONS, QUIZZES, QUIZ_QUESTIONS, WRITING_SUBMISSIONS, NOTIFICATIONS.
5. Corrected the USERS↔QUIZZES "attempt" relationship cardinality to **(0,N)–(0,N)**.

### 10.5 ⚠️ Open item on the MCD
The current, final MCD diagram **does not yet include a `WEEK` entity** between ROADMAPS and LESSONS, even though the MLD already introduces the weekly structure (`ROADMAP_WEEKS` / `ROADMAP_WEEK_LESSONS` — see §10.7). Two ways to handle this for the defense:
1. **Retrofit the MCD** — add a `WEEK` entity so the MCD→MLD transformation is fully traceable end-to-end (recommended if there's time before submission).
2. **Leave it and explain it** — treat the weekly breakdown as a refinement introduced only at the MLD stage, and have a clear, rehearsed explanation ready for the jury on why the conceptual model stays simpler while the logical model adds implementation-level structure.

---

### 10.6 Logical Model (MLD) — full table-by-table detail

The MLD resolves every MCD many-to-many relationship into a proper associative table, and adds the weekly-roadmap structure that doesn't appear in the MCD (see §10.5).

**`users`**
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| name | VARCHAR | |
| email | VARCHAR | |
| password | VARCHAR | |
| role | VARCHAR | `student` / `admin` |

**`placement_tests`**
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| user_id | INT | FK → users |
| submitted_at | TIMESTAMP | |
| status | VARCHAR | |
| cefr_level | VARCHAR | |
| grammar_score | DECIMAL | |
| vocabulary_score | DECIMAL | |
| writing_score | DECIMAL | |
| strengths | JSON | AI output |
| weaknesses | JSON | AI output |
| reasoning | TEXT | AI output |

> Note: earlier reviews flagged a leftover `reading_score` column here (a holdover from when Reading Comprehension was in scope). **That column has been removed** in the current, final MLD — it correctly reflects the Grammar/Vocabulary/Writing-only decision.

**`placement_questions`**
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| question | TEXT | |
| skill | VARCHAR | |
| level | VARCHAR | |

**`placement_answers`** — associative table resolving PLACEMENT_TESTS ↔ PLACEMENT_QUESTIONS
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| placement_test_id | INT | FK → placement_tests |
| placement_question_id | INT | FK → placement_questions |
| answer | TEXT | |
| score | DECIMAL | |
| feedback | TEXT | |

**`roadmaps`**
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| user_id | INT | FK → users |
| placement_test_id | INT | FK → placement_tests |
| title | VARCHAR | |
| generated_at | TIMESTAMP | |

**`roadmap_weeks`** — introduces the weekly structure (not yet mirrored in the MCD — see §10.5)
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| roadmap_id | INT | FK → roadmaps |
| week_number | INT | 1–4 |
| objective | TEXT | |

**`roadmap_week_lessons`** — which lessons belong to which roadmap week, in what order
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| roadmap_week_id | INT | FK → roadmap_weeks |
| lesson_id | INT | FK → lessons |
| display_order | INT | |

**`lessons`**
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| title | VARCHAR | |
| skill | VARCHAR | |
| level | VARCHAR | |

**`lesson_progress`** — associative table resolving USERS ↔ LESSONS
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| user_id | INT | FK → users |
| lesson_id | INT | FK → lessons |
| status | VARCHAR | |
| completed_at | TIMESTAMP | |

**`quizzes`**
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| lesson_id | INT | FK → lessons |
| title | VARCHAR | |
| description | TEXT | |

**`quiz_questions`**
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| quiz_id | INT | FK → quizzes |
| question | TEXT | |
| option_a | VARCHAR | |
| option_b | VARCHAR | |
| option_c | VARCHAR | |
| option_d | VARCHAR | |
| correct_answer | VARCHAR | |

**`quiz_attempts`** — associative table resolving USERS ↔ QUIZZES (each row = one attempt)
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| user_id | INT | FK → users |
| quiz_id | INT | FK → quizzes |
| score | DECIMAL | |
| completed_at | TIMESTAMP | |

**`writing_submissions`**
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| user_id | INT | FK → users |
| prompt | TEXT | |
| original_text | TEXT | |
| corrected_text | TEXT | |
| grammar_feedback | TEXT | AI output |
| vocabulary_feedback | TEXT | AI output |
| fluency_feedback | TEXT | AI output |
| mistakes | JSON | AI output |
| recommendations | JSON | AI output |
| next_topics | JSON | AI output |
| score | DECIMAL | |
| status | VARCHAR | |
| submitted_at | TIMESTAMP | |

**`notifications`**
| Column | Type | Notes |
|---|---|---|
| id | INT | PK |
| user_id | INT | FK → users |
| title | VARCHAR | |
| message | TEXT | |
| is_read | BOOLEAN | |
| created_at | TIMESTAMP | |

**`jobs` / `failed_jobs`** — standard Laravel queue infrastructure tables supporting all the async AI processing described in §7.5.

### 10.7 MLD Revision Summary
- All MCD N:N relationships resolved: `placement_answers`, `lesson_progress`, `quiz_attempts`.
- Added `roadmap_weeks` and `roadmap_week_lessons` to support the 4-week roadmap structure explicitly at the logical level (ahead of the MCD — see the open item in §10.5).
- Removed the leftover `reading_score` column from `placement_tests`.

---

## 11. API Endpoints (Documentation-Level)

| Method & Path | Purpose |
|---|---|
| `POST /register` | Create a student account |
| `POST /login` | Authenticate, obtain a Sanctum token |
| `POST /placement-tests` | Submit placement test answers (queues AI evaluation) |
| `GET /roadmap` | Retrieve the current 4-week roadmap |
| `GET /lessons` | List available lessons |
| `POST /lessons/{id}/complete` | Mark a lesson as completed |
| `GET /quizzes/{id}` | Retrieve a quiz's questions |
| `POST /quizzes/{id}/attempts` | Submit a quiz attempt |
| `POST /writing-submissions` | Submit a text for AI correction (queues AI job) |
| `GET /writing-submissions/{id}` | Retrieve a correction result |
| `GET /dashboard` | Retrieve progress indicators |
| Admin CRUD endpoints | Manage lessons, quizzes, quiz questions, placement questions (documented via Scribe) |

Full, authoritative endpoint documentation is generated with **Scribe** directly from route/controller annotations — this table is an index, not the source of truth.

---

## 12. Business Rules

| Code | Rule |
|---|---|
| BR01 | A student only sees their own test, roadmap, submissions, and progress |
| BR02 | The roadmap can only be generated after the placement test is fully analyzed |
| BR03 | A writing submission moves to `corrected` status only if the AI response matches the expected schema |
| BR04 | The AI can only recommend lessons/quizzes that exist in the catalog |
| BR05 | Only Admin can create, edit, or archive lessons, quizzes, and placement questions |
| BR06 | Scores are stored between 0 and 100; a failed AI call can be retried without limit, subject to rate limiting |

---

## 13. Technical Architecture (Full Breakdown)

### 13.1 Deployment topology (Azure)
```
Clients (Web browser / Mobile device / API client)
   ⇄ HTTPS
   ⇄ Azure Front Door (CDN, static content) [Azure Edge]
   ⇄ Azure App Service (Laravel application, PHP 8.x, RESTful API)
   ⇄ External Services: Groq API (AI analysis + text generation)
```
Groq is used specifically for AI analysis and text generation (placement evaluation, roadmap generation, writing correction).

### 13.2 Layered architecture
- **Presentation Layer** — client-facing surface (web/mobile/API consumers).
- **Application Layer** — orchestration of requests.
- **Business Layer** — the service layer, where domain logic lives (see §13.4).
- **Data Layer** — persistence (MySQL).
- **Infrastructure Layer** — hosting/cloud concerns (Azure, Docker).

### 13.3 Laravel request lifecycle (as diagrammed)
1. **Route** — `routes/api.php`, `routes/web.php`
2. **Authentication** — Sanctum middleware
3. **Middleware** — throttle requests, etc.
4. **Policy** — authorization
5. **Request** — Form Request validation
6. **Controller** — handles the request
7. **Response** — API Resource (JSON) or Blade view (HTML)

### 13.4 Service layer (business logic)
| Service | Responsibilities |
|---|---|
| `ProjectService` | Manage projects/hierarchy |
| `RoadmapService` | Generate roadmaps, AI recommendations |
| `QuizService` | Manage quizzes, questions, scoring |
| `LessonService` | Manage lessons, content delivery |
| `WritingService` | Submit writing, AI correction, feedback |
| `ReportService` | Progress reports, export (PDF/Excel) |
| `NotificationService` | In-app notifications, reminders |

### 13.5 Jobs & Queues (asynchronous processing)
Pipeline: **AI Analysis Job** (analyze writing via Groq API) → **Roadmap Generation Job** (generate personalized roadmap via AI) → **Notification Job** (send notification — email/in-app) → all backed by a **Database Queue** (Laravel Queue).

### 13.6 Database layer (persistent storage)
MySQL 8, tables: `users`, `placement_tests`, `placement_questions`, `placement_answers`, `lessons`, `quizzes`, `quiz_questions`, `quiz_attempts`, `lesson_progress`, `writing_submissions`, `notifications`. (Full column-level detail in §10.6.)

### 13.7 Monitoring & Logging
Azure Monitor.

### 13.8 Security (architecture-level)
- HTTPS (TLS)
- Azure WAF
- Sanctum authentication
- Roles & permissions
- Input validation
- Rate limiting

### 13.9 CI/CD flow
```
Developer → git push → GitHub
   → GitHub Actions (run tests: PHPUnit/Pest, lint, static analysis)
   → Docker build (build image & push to registry)
   → Azure App Service (deploy new image — automatic or manual)
```

### 13.10 Key technologies referenced in the architecture diagram
Laravel 13 (diagram legend still shows "Laravel 10" in one spot — treat Laravel 13 as authoritative per the current tech stack), PHP 8.x, MySQL 8, Azure, Docker, GitHub Actions.

---

## 14. Security (Application-Level, from the SRS)

- Hashed passwords; Sanctum-protected routes; Policies restrict access to a user's own data.
- Server-side validation on every form; `APP_DEBUG=false` in production.
- API keys only in `.env`, never committed to version control; rate limiting applied to AI routes specifically.
- Controlled fallback behavior and a clear error state if the AI service (Groq) is unavailable.

---

## 15. Testing & Acceptance Criteria

| ID | Criterion |
|---|---|
| AC01 | Auth flow works; protected routes return `401` without a valid token |
| AC02 | Placement test submission returns `202` and dispatches a Job (verified with `Queue::fake()`) |
| AC03 | Roadmap is generated only after a valid placement result exists |
| AC04 | Writing submission returns `422` on invalid input, `202` on success, and dispatches a Job |
| AC05 | AI calls are faked in tests (`AI::fake()`) — no real API calls happen during CI |
| AC06 | A student cannot access another student's data (verified with Policy tests) |
| AC07 | Dashboard endpoint returns `200` with the expected structure |

Testing is done with **Pest**, and CI runs the full suite on every push via GitHub Actions.

---

## 16. Deliverables Checklist

- [x] SRS v3.0 — Word (.docx) + Markdown, in both French and English
- [x] Project roadmap / sprint breakdown (scoping → defense prep, aligned to Aug 7 deadline)
- [x] MCD diagram (PNG/JPEG) — final reviewed version
- [x] MLD diagram (PNG/JPEG) — final reviewed version
- [x] System architecture diagram (PNG) — Azure deployment view
- [ ] GitHub repository with README (setup instructions, tests, live URL)
- [ ] Dockerfile, docker-compose.yml
- [ ] GitHub Actions CI/CD workflow (green check)
- [ ] API documentation via Scribe
- [ ] Defense presentation
- [ ] Live deployed URL (if deployment is completed before the defense)

(Checklist status reflects what's been discussed as completed so far; treat unchecked items as "not yet confirmed done" rather than "not planned.")

---

## 17. Version History — SRS v2.0 → v3.0

An earlier SRS draft (**v2.0, dated July 15, 2026**) is still floating around and should be treated as **superseded**. Key differences to be aware of, so the old file doesn't get confused with the current spec:

| Aspect | SRS v2.0 (outdated) | SRS v3.0 (current) |
|---|---|---|
| Placement test skills | Grammar, Vocabulary, **Reading**, Writing | Grammar, Vocabulary, Writing only |
| Laravel version | Laravel 11 | Laravel 13 |
| API docs tool | Not specified (Scribe/Swagger listed as either/or) | Scribe (settled choice) |
| `placement_tests` table | Included a `reading_score`-equivalent column implicitly | No reading-related column |
| Quizzes | Not modeled as a first-class relationship in the v2.0 data model summary | Quizzes are a full entity (`QUIZZES`, `QUIZ_QUESTIONS`, `QUIZ_ATTEMPTS`) tied to lessons |

**Recommendation:** archive or delete the v2.0 draft from the project folder so it isn't mistaken for the current source of truth during defense prep.

---

## 18. Known Open Items / Risks

1. **MCD is missing a `WEEK` entity** between ROADMAPS and LESSONS, even though the MLD already models the weekly breakdown via `roadmap_weeks` / `roadmap_week_lessons`. Needs a decision before the defense: retrofit the MCD, or prepare a clear verbal justification for introducing the refinement only at the MLD stage. (See §10.5.)
2. **Stale SRS v2.0 draft** still exists — risk of confusing reviewers or accidentally referencing outdated decisions (Reading Comprehension, Laravel 11) during the defense. Should be archived/deleted.
3. **Architecture diagram legend** shows "Laravel 10" in the key-technologies box while the rest of the project has standardized on Laravel 13 — worth a quick fix so the diagram is internally consistent.

---

## 19. Working Conventions (from prior projects in this track record)

These aren't unique to this project, but are Hassan's recurring habits worth keeping in mind when picking this project back up:
- Uses **OpenCode** as the AI coding agent, with a **Plan → Build → Review** discipline and AI-tagged commits.
- Frequent deliverable pattern across projects: Merise MCD/MLD data models, AI-diagram prompts, `AGENTS.md`/`agent.md`-style context files, OpenSpec-style config, and slide-deck presentation materials.
- Tests are written in **Pest**; queues use the **database driver**; AI integration goes through the **laravel/ai SDK with the Groq provider** — consistent with other recent Laravel projects (e.g. Expense Assistant, InterviewPrep).

---

## 20. Glossary

| Term | Meaning |
|---|---|
| CEFR | Common European Framework of Reference for Languages — the A1–C1 level scale used to grade learners |
| MCD | Modèle Conceptuel de Données — DBMS-independent conceptual data model (Merise method) |
| MLD | Modèle Logique de Données — logical data model where N:N relationships become associative tables and columns/keys are defined |
| Fil Rouge | French term for the capstone/thread-running-through-the-program project at the bootcamp |
| Sanctum | Laravel's lightweight API token/session authentication package |
| Groq | The AI provider used for all AI analysis/text-generation calls (placement evaluation, roadmap generation, writing correction) |
| Scribe | The tool used to auto-generate API documentation from Laravel routes/controllers |
| 202 Accepted | HTTP status returned immediately when an AI-triggering action is queued, before the AI result is ready |