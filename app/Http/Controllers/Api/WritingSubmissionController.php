<?php

namespace App\Http\Controllers\Api;

use App\Enums\WritingSubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitWritingSubmissionRequest;
use App\Http\Resources\WritingSubmissionResource;
use App\Jobs\CorrectWritingSubmission;
use App\Models\WritingSubmission;
use Illuminate\Http\JsonResponse;

class WritingSubmissionController extends Controller
{
    /**
     * Submit a piece of writing for AI correction.
     *
     * Dispatches the correction job and returns `202` immediately. The full
     * correction (corrected text, score, mistakes, next topics) arrives
     * asynchronously — poll the submission endpoint until status is
     * `corrected`.
     *
     * @group Writing Submissions
     *
     * @bodyParam prompt string required The writing prompt being answered. Example: Write a short paragraph introducing yourself.
     * @bodyParam original_text string required The student's text, at least 10 characters. Example: Hello, my name is Sara and I live in Casablanca.
     *
     * @response status=202 {
     *   "id": 1,
     *   "status": "pending"
     * }
     * @response status=422 {
     *   "message": "The original text must be at least 10 characters.",
     *   "errors": {"original_text": ["The original text must be at least 10 characters."]}
     * }
     */
    public function store(SubmitWritingSubmissionRequest $request): JsonResponse
    {
        $submission = WritingSubmission::create([
            'user_id' => $request->user()->id,
            'prompt' => $request->prompt,
            'original_text' => $request->original_text,
            'submitted_at' => now(),
        ]);

        CorrectWritingSubmission::dispatch($submission);

        return response()->json([
            'id' => $submission->id,
            'status' => WritingSubmissionStatus::Pending->value,
        ], 202);
    }

    /**
     * Retrieve a writing submission and its AI correction.
     *
     * @group Writing Submissions
     *
     * @urlParam writing_submission integer required The submission id. Example: 1
     *
     * @apiResource App\Http\Resources\WritingSubmissionResource
     *
     * @apiResourceModel App\Models\WritingSubmission
     */
    public function show(WritingSubmission $writingSubmission): WritingSubmissionResource
    {
        $this->authorize('view', $writingSubmission);

        return new WritingSubmissionResource($writingSubmission);
    }
}
