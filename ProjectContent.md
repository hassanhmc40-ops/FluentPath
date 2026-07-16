# ENGLISH MENTOR AI
### A Personalized AI English Coach

**SOFTWARE REQUIREMENTS SPECIFICATION**
Capstone Project — AI-Augmented Backend
Simplon Maghreb × JobinTech Bootcamp — July 2026 Cohort

Prepared by: Hassan
Version 3.0 — July 15, 2026

---

## 0. Document Overview

| Field | Value |
|---|---|
| Project | English Mentor AI |
| Document type | Software Requirements Specification (SRS) |
| Author | Hassan |
| Audience | Self-taught English learners, A1 to C1 |
| Stack | Laravel 13, PHP 8.3, MySQL 8, Sanctum, Laravel AI SDK, Docker, Pest, GitHub Actions, Azure |
| Version | 3.0 — July 15, 2026 |

---

## 1. Overview & Problem

English Mentor AI is a web application that diagnoses a learner's real English level, builds a personalized 4-week roadmap, recommends the most relevant lessons and quizzes, and corrects written work — adapting continuously as the learner progresses. Most existing platforms deliver the same fixed curriculum to everyone, so learners rarely know their real level, what to prioritize, or why they keep repeating the same mistakes.

> Personalization is the core value of the application. All educational content — lessons and quizzes — is authored by the Administrator; the AI's role is to guide the learner through an adaptive journey by evaluating, planning and recommending, not to generate the content itself.

---

## 2. Objectives

| Code | Objective |
|---|---|
| O1 | Assess the learner's real English level through a holistic placement test. |
| O2 | Generate a personalized 4-week roadmap from that assessment. |
| O3 | Recommend existing lessons and quizzes based on the learner's evolving profile. |
| O4 | Correct written submissions with detailed, explained feedback. |
| O5 | Track and visualize progress in a single dashboard. |
| O6 | Run every AI call asynchronously (Jobs/Queues) and never block the UI. |
| O7 | Keep every AI output structured, validated, and safely stored. |
| O8 | Keep lesson and quiz authoring under full Administrator control in the MVP. |

---

## 3. Scope

### In scope
- Auth & profile management (Student, Admin) — Sanctum.
- Holistic AI placement test (grammar, vocabulary, writing).
- AI-generated 4-week personalized roadmap.
- Lesson catalog, fully authored and managed by the Administrator.
- Quiz catalog, authored by the Administrator and linked to lessons and learning objectives.
- Writing module with detailed AI correction.
- Recommendation engine, updated after every lesson, quiz or writing submission.
- Progress dashboard and in-app notifications.
- Scheduled task for daily recommendations / inactivity reminders.

### Out of scope (MVP)
- Reading comprehension assessment — would require audio or more advanced content; deferred.
- Pronunciation assessment (audio analysis) and real-time AI conversation simulator.
- Native mobile app, payments, social/leaderboard features.
- AI-powered lesson generation — see Section 18, Future Improvements.
- Teacher role and class management — optional, long-term idea only, not part of the MVP.

---

## 4. Users & Roles

| Role | Responsibilities |
|---|---|
| Student | Registers, takes the placement test, follows the roadmap, completes lessons and quizzes, submits writing, reviews AI feedback, tracks progress. |
| Admin | Authors and manages the lesson and quiz catalog, monitors platform usage. Never edits an individual student's roadmap, recommendations or writing corrections — those stay fully automated. |

---

## 5. User Stories

### Student
- As a student, I want to register so that I can access my personalized learning space.
- As a student, I want to take a placement test so that the AI can determine my English level.
- As a student, I want a personalized roadmap so that I know what to study next.
- As a student, I want to take quizzes linked to my lessons so that I can check my understanding.
- As a student, I want the AI to correct my writing and explain my mistakes so that I can improve.
- As a student, I want to track my progress so that I can monitor my improvement over time.
- As a student, I want to be notified when new feedback or recommendations are ready.

### Admin
- As an admin, I want to create and manage lessons so that students always have quality content.
- As an admin, I want to create and manage quizzes linked to lessons and learning objectives so that students can validate their understanding.
- As an admin, I want to monitor platform usage so that I can assess engagement.

---

## 6. Core Functionalities

| ID | Feature | Description | Priority |
|---|---|---|---|
| F01 | Authentication | Register, login, logout, profile (Sanctum). | High |
| F02 | Placement test | Grammar, vocabulary and writing questions submitted together. | High |
| F03 | AI evaluation | Holistic CEFR assessment with reasoning, strengths and weaknesses. | High |
| F04 | AI roadmap | 4-week personalized learning plan. | High |
| F05 | Lesson catalog | Admin-authored lessons; students complete them and track progress. | High |
| F06 | Quiz catalog | Admin-authored quizzes linked to lessons and learning objectives. | High |
| F07 | Writing correction | Detailed AI feedback on free-form text. | High |
| F08 | Recommendation engine | Updates next lesson, quiz or writing focus after every activity. | High |
| F09 | Dashboard | Progress indicators and history. | High |
| F10 | Notifications | Alerts when AI results are ready. | Medium |
| F11 | Admin content management | CRUD on lessons and quizzes. | Medium |

### 6.1 Lesson Management
- The Administrator manually creates and manages all lessons.
- Lessons are stored in the database.
- The AI does not generate lessons as part of the MVP.
- The AI only recommends the most appropriate existing lessons based on the learner's profile and progress.

### 6.2 Quiz Management
- The Administrator manually creates and manages all quizzes.
- Quizzes are linked to lessons and learning objectives.
- The AI does not generate quizzes in the MVP.

---

## 7. AI Responsibilities (Core Value)

Personalization is the core value of English Mentor AI. The AI never authors lessons or quizzes in the MVP — it guides the learner through an adaptive journey. Specifically, the AI is responsible for:

- Evaluating the learner's placement test.
- Determining the learner's CEFR level.
- Identifying strengths and weaknesses.
- Explaining the reasoning behind the evaluation.
- Generating a personalized learning roadmap.
- Correcting writing exercises with detailed feedback.
- Recommending the next lessons and quizzes after each completed activity.
- Continuously updating recommendations according to the learner's progress.

### 7.1 Placement Test Evaluation (Grammar, Vocabulary, Writing)

The placement test is not a simple multiple-choice quiz: it combines grammar, vocabulary and writing questions. Reading comprehension is intentionally excluded from the MVP, as a proper implementation would require audio or more advanced content beyond this project's scope. The AI reviews the full submission the way a human teacher would, rather than just scoring closed answers, and returns:

- CEFR level (A1–C1)
- Per-skill scores (grammar, vocabulary, writing)
- Strengths and weaknesses
- A short written justification of the assessment
- Initial recommendations feeding directly into the roadmap

### 7.2 4-Week Personalized Roadmap

From the placement result, the AI generates a 4-week roadmap. Each week includes: an objective, recommended lessons, grammar topics, vocabulary topics, a writing activity, and suggested quizzes — all referencing content that already exists in the catalog.

### 7.3 Recommendation Engine

After every completed activity (lesson, quiz, or writing submission), the recommendation engine re-evaluates the learner's profile and refreshes the next lesson, the next quiz, and the next writing focus. It selects and orders existing catalog content; it never creates new content.

### 7.4 Writing Correction

Submitted texts are corrected asynchronously. The AI returns:

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
- Every AI call (test, roadmap, correction) runs through a Job — the user gets an immediate 202 response, never a blocking call.
- Responses must match a strict JSON schema (Laravel AI SDK structured output); non-conforming responses are rejected and logged.
- Results are stored via Eloquent Casts; API keys stay in `.env` only.
- The AI can only recommend lessons and quizzes that exist in the database and never publishes content on its own.
- Rate limiting applies to every AI-triggering route; a failed call lets the student retry.

---

## 8. User Journey — Adaptive Learning Cycle

**Register → Placement Test → AI Evaluation → Personalized Roadmap → Lessons → Quizzes → Writing Practice → AI Feedback → Dashboard Update → Personalized Recommendations → Continuous Learning.**

This is a loop, not a linear flow: every writing submission, lesson or quiz completion feeds back into the recommendation engine, which keeps the roadmap and next steps aligned with the learner's actual progress.

---

## 9. Dashboard & Progress Tracking
- Current CEFR level and roadmap week.
- Completed lessons and quizzes vs. total.
- Writing score history (trend over time).
- Grammar and vocabulary improvement indicators.
- Current learning streak (consecutive active days).
- Overall progress percentage and next recommended action.

---

## 10. Notifications

A simple notification system informs the student when an asynchronous AI result becomes available or when new recommendations are ready:

- Placement test analyzed.
- Personalized roadmap generated.
- Writing correction completed.
- New recommendations available.

---

## 11. Business Rules

| Code | Rule |
|---|---|
| BR01 | A student only sees their own test, roadmap, submissions and progress. |
| BR02 | The roadmap can only be generated after the placement test is fully analyzed. |
| BR03 | A writing submission moves to 'corrected' only if the AI response matches the expected schema. |
| BR04 | The AI can only recommend lessons and quizzes that already exist in the catalog. |
| BR05 | Only Admin can create, edit or archive lessons and quizzes. |
| BR06 | Scores are stored between 0 and 100; a failed AI call can be retried without limit, subject to rate limiting. |
| BR07 | Any bonus AI-generated lesson (Section 18) must be reviewed and validated by the Administrator before publication. |

---

## 12. Data Model (Simplified)

| Table | Purpose |
|---|---|
| users | Students and admins — name, email, password, role. |
| placement_tests | Submitted answers plus the AI's structured result (level, scores, strengths, weaknesses, reasoning) as JSON casts. |
| roadmaps | AI-generated 4-week plan (objectives, lessons, topics) as JSON. |
| lessons | Admin-authored catalog: title, skill, level, content. |
| quizzes | Admin-authored quizzes: linked lesson, learning objective, questions. |
| activity_progress | Student ↔ lesson/quiz completion and quiz scores. |
| writing_submissions | Original text, status, and the AI's structured feedback (corrected text, scores, mistakes, recommendations) as JSON casts. |
| notifications | Type, message, read status, per user. |
| jobs / failed_jobs | Queue infrastructure for asynchronous AI processing. |

---

## 13. API Endpoints (Documentation Only)

| Method & Path | Purpose |
|---|---|
| POST /register | Create a student account. |
| POST /login | Authenticate and obtain a Sanctum token. |
| POST /placement-tests | Submit placement test answers (queues AI evaluation). |
| GET /roadmap | Retrieve the current 4-week roadmap. |
| GET /lessons | List available lessons. |
| POST /lessons/{id}/complete | Mark a lesson as completed. |
| GET /lessons/{id}/quizzes | List quizzes linked to a lesson. |
| POST /quizzes/{id}/attempts | Submit a quiz attempt and receive a score. |
| POST /writing-submissions | Submit a text for AI correction. |
| GET /writing-submissions/{id} | Retrieve a correction result. |
| GET /dashboard | Retrieve progress indicators. |

---

## 14. Technology Stack

| Layer | Choice |
|---|---|
| Backend | Laravel 13, PHP 8.3. |
| Database | MySQL 8. |
| Authentication | Laravel Sanctum. |
| AI | Laravel AI SDK, structured output. |
| Queue | Laravel Jobs & Queues. |
| Scheduled tasks | Laravel Scheduler — daily recommendations / inactivity reminders. |
| Testing | Pest PHP. |
| API documentation | Scribe (or Swagger if needed). |
| Containerization | Docker, Docker Compose. |
| CI/CD | GitHub Actions — tests run on every push. |
| Deployment | Azure App Service, Azure Database for MySQL (or selected platform). |
| Version control | Git, GitHub. |

---

## 15. Security
- Hashed passwords; Sanctum-protected routes; Policies restrict access to own data.
- Server-side validation on every form; `APP_DEBUG=false` in production.
- API keys only in `.env`, never committed; rate limiting on AI routes.
- Controlled fallback and clear error state if the AI service is unavailable.

---

## 16. Testing & Acceptance Criteria

| ID | Criterion |
|---|---|
| AC01 | Auth flow works; protected routes return 401 without a valid token. |
| AC02 | Placement test submission returns 202 and dispatches a Job (`Queue::fake()`). |
| AC03 | Roadmap is generated only after a valid placement result exists. |
| AC04 | Writing submission returns 422 on invalid input, 202 on success, and dispatches a Job. |
| AC05 | AI calls are faked in tests (`AI::fake()`) — no real API calls during CI. |
| AC06 | A student cannot access another student's data (Policy tests). |
| AC07 | Dashboard endpoint returns 200 with the expected structure. |

---

## 17. Deliverables
- GitHub repository with README (setup, tests, live URL).
- MCD, MLD and architecture diagrams (PNG/JPEG).
- Dockerfile, docker-compose, GitHub Actions workflow (green check).
- Jira board link, kept up to date.
- API documentation (Scribe/Swagger).
- Defense presentation and, if deployed, the live URL.

---

## 18. Future Improvements / Bonus Features

### 18.1 AI Lesson Generator (Bonus)

If time allows, the Administrator can use an AI-powered lesson generator. This feature is entirely outside the MVP and does not replace the manual lesson and quiz workflow described in Sections 6.1 and 6.2.

The Administrator provides:
- Topic
- CEFR level
- Skill (Grammar, Vocabulary, Writing)

The AI generates:
- Lesson explanation
- Learning objectives
- Examples
- Practice exercises
- Quiz suggestions
- Writing prompt

> The generated content must always be reviewed and validated by the Administrator before publication — the AI never publishes a lesson or quiz directly, even in this bonus workflow.

### 18.2 Other long-term ideas
- Reading comprehension assessment, once audio or richer content is in scope.
- Pronunciation assessment and a real-time AI conversation simulator.
- Teacher role with class management.

---

## 19. Conclusion

English Mentor AI succeeds if the MVP scope is respected: a working, tested Laravel application where the Administrator owns all educational content, and the AI — not a static rule set — diagnoses the learner, builds their roadmap, and reviews their writing, with every call asynchronous, validated and explainable.