<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public const GOALS = [
        'Work & interviews' => 'Emails, meetings, reporting',
        'Study & exams' => 'IELTS, TOEFL, university',
        'Travel & daily life' => 'Conversation and confidence',
        'Not sure yet' => 'Let the test decide',
    ];

    public const HOURS = [
        '1-2 hours' => 'Light, one lesson a week',
        '3-4 hours' => 'Recommended pace',
        '5-7 hours' => 'Fast track',
        '8+ hours' => 'Intensive',
    ];

    public const STRUGGLES = [
        'Grammar rules' => 'Tenses, articles, prepositions',
        'Vocabulary' => "Words don't come fast enough",
        'Writing' => "Ideas don't connect",
        'Reading speed' => 'Long texts are slow',
    ];

    public function show(Request $request): View
    {
        return view('onboarding', [
            'goals' => self::GOALS,
            'hours' => self::HOURS,
            'struggles' => self::STRUGGLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'goal' => ['required', 'string', 'in:'.implode(',', array_keys(self::GOALS))],
            'weekly_hours' => ['required', 'string', 'in:'.implode(',', array_keys(self::HOURS))],
            'struggle' => ['required', 'string', 'in:'.implode(',', array_keys(self::STRUGGLES))],
        ]);

        $request->user()->update([
            'onboarding_goal' => $request->input('goal'),
            'weekly_hours' => $request->input('weekly_hours'),
            'struggle' => $request->input('struggle'),
        ]);

        return redirect('/placement-test');
    }
}
