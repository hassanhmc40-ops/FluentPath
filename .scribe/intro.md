# Introduction

English Mentor AI (FluentPath) — holistic CEFR placement test, AI-generated 4-week roadmap and AI writing correction. Every AI call runs asynchronously (Laravel jobs + database queue) and results are validated against strict JSON schemas.

<aside>
    <strong>Base URL</strong>: <code>http://localhost</code>
</aside>

    This API powers the English Mentor AI web app. Students register, take the placement test, receive a personalized roadmap, complete lessons, attempt quizzes and submit writing for AI correction — all asynchronously.

    **Authentication:** most endpoints require a *Sanctum bearer token*. Create accounts and log in via `POST /api/register` and `POST /api/login` to obtain one, then send it as an `Authorization: Bearer <token>` header.

    **Asynchronous AI:** `POST /api/placement-tests`, `POST /api/roadmaps` and `POST /api/writing-submissions` return `202 Accepted` immediately and process the AI call in a queued job.

