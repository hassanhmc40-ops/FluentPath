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

    public function show(WritingSubmission $writingSubmission): WritingSubmissionResource
    {
        $this->authorize('view', $writingSubmission);

        return new WritingSubmissionResource($writingSubmission);
    }
}
