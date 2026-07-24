# agent.md — English Mentor AI ("FluentPath")

> Reference document for anyone (human or AI agent) working on this codebase.
> This reflects the **current, final decisions** (SRS v3.0 + final MCD/MLD diagrams).
> ⚠️ Note: an earlier SRS v2.0 draft (July 15, 2026) still floats around the project folder — it still lists Reading Comprehension in the placement test and references Laravel 11. That draft is **superseded**. Everything below uses the current decisions: no Reading Comprehension, Laravel 13.

---

## 1. Project Identity

| | |
|---|---|
| **Name** | English Mentor AI, internally referred to as "FluentPath" in diagrams |
| **Type** | Capstone / Fil Rouge project — Simplon Maghreb × JobinTech Bootcamp |
| **Author** | Hassan |
| **Pitch** | A web app that diagnoses a learner's real English level, builds a personalized 4-week roadmap, recommends targeted lessons/quizzes, and corrects written work — all continuously adapted by AI |
| **Submission deadline** | August 7, 2026 |
| **Oral defense (soutenance)** | From August 10, 2026 |

**Core principle:** the AI is the product, not an add-on. It evaluates the placement test holistically, generates the roadmap, and reviews every piece of writing. Remove the AI and the application has no reason to exist.

---

## 2. Objectives

| Code | Objective |
|---|---|
| O1 | Assess the learner's real English level through a holistic placement test |
| O2 | Generate a personalized 4-week roadmap from that assessment |
| O3 | Recommend existing lessons/exercises based on the learner's evolving profile |
| O4 | Correct written submissions with detailed, explained feedback |
| O5 | Track and visualize progress in a single dashboard |
| O6 | Run every AI call asynchronously (Jobs/Queues) — never block the UI |
| O7 | Keep every AI output structured, validated, and safely stored |

---

## 3. Scope

### In scope (MVP)
- Auth & profile management (Student, Admin) via Sanctum
- Holistic AI placement test — **Grammar, Vocabulary, Writing only**
- AI-generated 4-week personalized roadmap
- Lesson catalog (Admin-managed) with completion tracking
- Quizzes tied to lessons
- Writing module with detailed AI correction
- Recommendation engine, refreshed after every learner activity
- Progress dashboard + in-app notifications
- Scheduled task for daily recommendations / inactivity reminders

### Explicitly out of scope (MVP)
- **Reading Comprehension** as a placement-test skill (dropped from earlier draft)
- Pronunciation assessment (audio analysis)
- Real-time AI conversation simulator
- Native mobile app, payments, social/leaderboard features
- **Teacher role** and class management — long-term idea only, not in MVP
- AI-generated lesson/quiz *content* — documented as a bonus/future feature only. In the MVP, the Admin authors all lessons and quizzes manually; the AI only selects and sequences existing content.

---

## 4. Roles

| Role | Responsibilities |
|---|---|
| **Student** | Registers, takes the placement test, follows the roadmap, completes lessons/quizzes, submits writing, reviews AI feedback, tracks progress |
| **Admin** | Creates and manages the lesson/quiz/placement-question catalog, monitors platform usage. Never manually edits an individual student's roadmap — that stays fully automated |

No Teacher role in the MVP.

---

## 5. Tech Stack

| Layer | Choice |
|---|---|
| Backend | Laravel 13, PHP 8.3 |
| Database | MySQL 8 |
| Auth | Laravel Sanctum |
| AI | Laravel AI SDK, **Groq** provider, structured JSON output |
| Async processing | Jobs & Queues (database driver) |
| Scheduling | Laravel Scheduler (daily recommendations / inactivity reminders) |
| Testing | Pest |
| Containerization | Docker + Docker Compose (app, MySQL, worker) |
| CI/CD | GitHub Actions (tests run on every push) |
| Deployment | Azure App Service + Azure Database for MySQL, behind Azure Front Door (CDN/edge) |
| API docs | Scribe |

---

## 6. Core Features

| ID | Feature | Priority |
|---|---|---|
| F01 | Authentication (register/login/logout/profile) | High |
| F02 | Placement test — Grammar, Vocabulary, Writing questions submitted together | High |
| F03 | AI evaluation — holistic CEFR assessment with reasoning, strengths, weaknesses | High |
| F04 | AI-generated 4-week roadmap | High |
| F05 | Lessons & quizzes (Admin-managed catalog; students complete them) | High |
| F06 | Writing correction — detailed AI feedback on free-form text | High |
| F07 | Recommendation engine — updates next lesson/topics after every activity | High |
| F08 | Dashboard — progress indicators and history | High |
| F09 | Notifications — alerts when AI results are ready | Medium |
| F10 | Admin content management — CRUD on lessons, quizzes, placement questions | Medium |

---

## 7. AI Features & Guardrails

### 7.1 Holistic Placement Test Evaluation
Grammar, Vocabulary, and Writing questions are submitted together and reviewed holistically (like a human teacher), not scored as isolated multiple-choice items. The AI returns:
- CEFR level (A1–C1)
- Per-skill scores (grammar, vocabulary, writing)
- Strengths and weaknesses
- A short written justification of the assessment
- Initial recommendations feeding into the roadmap

### 7.2 4-Week Personalized Roadmap
Generated from the placement result. Structured as 4 weeks, each with an objective, recommended lessons, grammar/vocabulary topics, a writing activity, and suggested quizzes. **Lessons/quizzes referenced must already exist in the catalog — the AI never invents content in the MVP.**

### 7.3 Lesson/Quiz Catalog & Recommendation Engine
All lessons and quizzes are created and maintained by the Admin. The AI only selects and orders existing content matching the learner's profile. After every completed activity (lesson, quiz, or writing submission), the recommendation engine re-evaluates the profile and refreshes next steps.

### 7.4 Writing Correction
Submitted texts are corrected asynchronously. Example AI response shape:
```json
{
  "corrected_text": "Yesterday I went to school.",
  "score": 82,
  "grammar_feedback": "Past Simple required after \"yesterday\".",
  "vocabulary_feedback": "Good range; consider more varied connectors.",
  "fluency_feedback": "Sentences are short; try combining ideas.",
  "mistakes": [{ "original": "go", "correction": "went", "rule": "Past Simple" }],
  "recommendations": ["Review Past Simple", "Practice linking words"],
  "next_topics": ["Past Simple", "Irregular verbs"]
}
```

### 7.5 AI Workflow & Guardrails
- Every AI call (test evaluation, roadmap generation, writing correction) runs through a Job — the user gets an immediate `202` response, never a blocking call.
- Responses must match a strict JSON schema (structured output); non-conforming responses are rejected and logged.
- Results are stored via Eloquent casts; API keys stay in `.env` only.
- The AI can only recommend lessons/quizzes that exist in the database, and never sets the final grade or unlocks content on its own — the application layer validates before storing.
- Rate limiting applies to every AI-triggering route; a failed call lets the student retry.

---

## 8. User Journey (Adaptive Learning Cycle)

```
Register → Placement Test → AI Evaluation → Personalized Roadmap
   → Lessons → Quizzes → Writing Practice → AI Feedback
   → Dashboard Update → Personalized Recommendations → (loop)
```

This is a **loop**, not a linear flow — every writing submission or completed lesson/quiz feeds back into the recommendation engine, keeping the roadmap aligned with actual progress.

---

## 9. Data Model — Conceptual (MCD)

Final reviewed MCD (Merise notation), entities in plural form:

- **USERS** — id, name, email, password, role. Central entity (Student/Admin).
- **PLACEMENT_TESTS** — id, submitted_at, status, cefr_level, grammar_score, vocabulary_score, writing_score, strengths, weaknesses, reasoning.
- **PLACEMENT_QUESTIONS** — id, question, skill, level.
- **ROADMAPS** — id, title, objective, generated_at.
- **LESSONS** — id, title, skill, level.
- **QUIZZES** — id, title, description.
- **QUIZ_QUESTIONS** — id, question, option_a–d, correct_answer.
- **WRITING_SUBMISSIONS** — id, prompt, original_text, corrected_text, score, status.
- **NOTIFICATIONS** — id, title, message, is_read.

### Relationships & cardinalities
| Relationship | Cardinality | Note |
|---|---|---|
| USERS → PLACEMENT_TESTS | (0,N)–(1,1) | `take` |
| PLACEMENT_TESTS ↔ PLACEMENT_QUESTIONS | (1,N)–(0,N) | `contain` — resolved via PLACEMENT_ANSWERS in MLD |
| PLACEMENT_TESTS → ROADMAPS | (0,1)–(1,1) | `generate` |
| USERS → ROADMAPS | (0,N)–(1,1) | `have` |
| ROADMAPS ↔ LESSONS | (1,N)–(0,N) | `recommend` |
| USERS ↔ LESSONS | (0,N)–(0,N) | `complete` — resolved via LESSON_PROGRESS |
| LESSONS → QUIZZES | (0,N)–(1,1) | `contain` |
| USERS ↔ QUIZZES | (0,N)–(0,N) | `attempt` — resolved via QUIZ_ATTEMPTS |
| QUIZZES → QUIZ_QUESTIONS | (1,N)–(1,1) | `contain` |
| USERS → WRITING_SUBMISSIONS | (0,N)–(1,1) | `submit` |
| USERS → NOTIFICATIONS | (0,N)–(1,1) | `receive` |

**⚠️ Open item — not yet fixed:** the MCD does not show the roadmap's weekly structure (no `WEEK` entity between ROADMAPS and LESSONS), even though the MLD already introduces `ROADMAP_WEEKS`/`ROADMAP_WEEK_LESSONS`. Two options for the defense:
1. Add a `WEEK` entity to the MCD so the MCD→MLD transformation is fully traceable, **or**
2. Treat it as a refinement introduced only at the MLD stage, and be ready to explain that design choice live.

---

## 10. Data Model — Logical (MLD)

All MCD many-to-many relationships are resolved into associative tables. Final MLD tables:

| Table | Key columns | Purpose |
|---|---|---|
| `users` | id (PK), name, email, password, role | Students & admins |
| `placement_tests` | id (PK), user_id (FK), submitted_at, status, cefr_level, grammar_score, vocabulary_score, writing_score, strengths (JSON), weaknesses (JSON), reasoning | *(no `reading_score` column — resolved)* |
| `placement_questions` | id (PK), question, skill, level | Question bank |
| `placement_answers` | id (PK), placement_test_id (FK), placement_question_id (FK), answer, score, feedback | Associative table resolving PLACEMENT_TESTS↔PLACEMENT_QUESTIONS |
| `roadmaps` | id (PK), user_id (FK), placement_test_id (FK), title, generated_at | |
| `roadmap_weeks` | id (PK), roadmap_id (FK), week_number, objective | Weekly structure of the 4-week roadmap |
| `roadmap_week_lessons` | id (PK), roadmap_week_id (FK), lesson_id (FK), display_order | Lessons assigned to each roadmap week |
| `lessons` | id (PK), title, skill, level | Admin-managed catalog |
| `lesson_progress` | id (PK), user_id (FK), lesson_id (FK), status, completed_at | Resolves USERS↔LESSONS |
| `quizzes` | id (PK), lesson_id (FK), title, description | |
| `quiz_questions` | id (PK), quiz_id (FK), question, option_a–d, correct_answer | |
| `quiz_attempts` | id (PK), user_id (FK), quiz_id (FK), score, completed_at | Resolves USERS↔QUIZZES |
| `writing_submissions` | id (PK), user_id (FK), prompt, original_text, corrected_text, grammar_feedback, vocabulary_feedback, fluency_feedback, mistakes (JSON), recommendations (JSON), next_topics (JSON), score, status, submitted_at | |
| `notifications` | id (PK), user_id (FK), title, message, is_read, created_at | |
| `jobs` / `failed_jobs` | — | Queue infrastructure for async AI processing |

---

## 11. API Endpoints (indicative — document fully with Scribe)

| Method & Path | Purpose |
|---|---|
| `POST /register` | Create a student account |
| `POST /login` | Authenticate, obtain Sanctum token |
| `POST /placement-tests` | Submit placement test answers (queues AI evaluation) → `202` |
| `GET /roadmap` | Retrieve current 4-week roadmap |
| `GET /lessons` | List available lessons |
| `POST /lessons/{id}/complete` | Mark a lesson as completed |
| `GET /quizzes/{id}` | Retrieve quiz questions |
| `POST /quizzes/{id}/attempts` | Submit a quiz attempt |
| `POST /writing-submissions` | Submit text for AI correction (queues job) → `202` |
| `GET /writing-submissions/{id}` | Retrieve correction result |
| `GET /dashboard` | Retrieve progress indicators |
| Admin CRUD routes | Manage lessons, quizzes, placement questions |

---

## 12. Business Rules

| Code | Rule |
|---|---|
| BR01 | A student only sees their own test, roadmap, submissions, and progress |
| BR02 | The roadmap can only be generated after the placement test is fully analyzed |
| BR03 | A writing submission moves to `corrected` only if the AI response matches the expected schema |
| BR04 | The AI can only recommend lessons/quizzes that exist in the catalog |
| BR05 | Only Admin can create, edit, or archive lessons, quizzes, and placement questions |
| BR06 | Scores are stored between 0 and 100; a failed AI call can be retried without limit, subject to rate limiting |

---

## 13. System Architecture

**Deployment (Azure):**
```
Clients (Browser / Mobile / API) → HTTPS → Azure Front Door (CDN, static content)
   → Azure App Service (Laravel app, RESTful API) → Groq API (AI analysis / text generation)
```

**Layered architecture:**
- Presentation Layer
- Application Layer
- Business Layer (Service Layer)
- Data Layer
- Infrastructure Layer

**Laravel request lifecycle:** Route → Authentication (Sanctum) → Middleware (throttling, etc.) → Policy (authorization) → Request (Form Request validation) → Controller → Response (API Resource / Blade).

**Service layer (business logic):**
- `ProjectService` — manage projects/hierarchy
- `RoadmapService` — generate roadmaps, AI recommendations
- `QuizService` — manage quizzes, questions, scoring
- `LessonService` — manage lessons, content delivery
- `WritingService` — submit writing, AI correction, feedback
- `ReportService` — progress reports, export (PDF/Excel)
- `NotificationService` — in-app notifications, reminders

**Jobs & Queues (async processing):** AI Analysis Job (Groq) → Roadmap Generation Job → Notification Job → Database Queue (Laravel Queue).

**Database layer:** MySQL 8 — `users`, `placement_tests`, `placement_questions`, `placement_answers`, `lessons`, `quizzes`, `quiz_questions`, `quiz_attempts`, `lesson_progress`, `writing_submissions`, `notifications`.

**Security:** HTTPS (TLS), Azure WAF, Sanctum authentication, roles & permissions, input validation, rate limiting.

**Monitoring:** Azure Monitor.

**CI/CD flow:** `git push` → GitHub → GitHub Actions (PHPUnit/Pest, lint, analysis) → Docker build & push to registry → Azure App Service deploy (automatic/manual).

---

## 14. Security (general)

- Hashed passwords; Sanctum-protected routes; Policies restrict access to own data
- Server-side validation on every form; `APP_DEBUG=false` in production
- API keys only in `.env`, never committed; rate limiting on AI routes
- Controlled fallback and clear error state if the AI service is unavailable

---

## 15. Testing & Acceptance Criteria

| ID | Criterion |
|---|---|
| AC01 | Auth flow works; protected routes return `401` without a valid token |
| AC02 | Placement test submission returns `202` and dispatches a Job (`Queue::fake()`) |
| AC03 | Roadmap is generated only after a valid placement result exists |
| AC04 | Writing submission returns `422` on invalid input, `202` on success, and dispatches a Job |
| AC05 | AI calls are faked in tests (`AI::fake()`) — no real API calls during CI |
| AC06 | A student cannot access another student's data (Policy tests) |
| AC07 | Dashboard endpoint returns `200` with the expected structure |

---

## 16. Deliverables

- GitHub repository with README (setup, tests, live URL)
- SRS (v3.0) — Word + Markdown, French + English
- Project roadmap / sprint breakdown
- MCD, MLD, and system architecture diagrams (PNG/JPEG)
- Dockerfile, docker-compose, GitHub Actions workflow (green check)
- API documentation (Scribe)
- Defense presentation, and live URL if deployed

---

## 17. Known Open Items (as of this document)

1. **MCD missing `WEEK` entity** between ROADMAPS and LESSONS — MLD already has the weekly tables (`roadmap_weeks`, `roadmap_week_lessons`); decide whether to retrofit the MCD or explain the refinement at the MLD stage during the defense.
2. **Stale SRS v2.0 draft** still exists alongside the current v3.0 — worth deleting or clearly archiving so it isn't mistaken for the current spec (it still lists Reading Comprehension and Laravel 11).