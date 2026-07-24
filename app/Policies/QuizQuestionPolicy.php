<?php

namespace App\Policies;

use App\Models\QuizQuestion;
use App\Models\User;

class QuizQuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, QuizQuestion $quizQuestion): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, QuizQuestion $quizQuestion): bool
    {
        return false;
    }

    public function delete(User $user, QuizQuestion $quizQuestion): bool
    {
        return false;
    }
}
