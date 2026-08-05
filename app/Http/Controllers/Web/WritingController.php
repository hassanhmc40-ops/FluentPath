<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitWritingSubmissionRequest;
use App\Jobs\CorrectWritingSubmission;
use App\Models\WritingSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WritingController extends Controller
{
    public function index(Request $request): View
    {
        $submissions = WritingSubmission::where('user_id', $request->user()->id)
            ->orderByDesc('submitted_at')
            ->get();

        return view('writing.index', compact('submissions'));
    }

    public function submissions(Request $request): View
    {
        $submissions = WritingSubmission::where('user_id', $request->user()->id)
            ->orderByDesc('submitted_at')
            ->get();

        return view('writing.submissions', compact('submissions'));
    }

    public function store(SubmitWritingSubmissionRequest $request): RedirectResponse
    {
        $submission = WritingSubmission::create([
            'user_id' => $request->user()->id,
            'prompt' => $request->prompt,
            'original_text' => $request->original_text,
            'submitted_at' => now(),
        ]);

        CorrectWritingSubmission::dispatch($submission);

        return back()->with('success', 'Writing submitted. Correction started — check back shortly.');
    }
}
