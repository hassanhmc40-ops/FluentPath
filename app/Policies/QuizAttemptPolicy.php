<?php

namespace App\Policies;

use App\Models\QuizAttempt;
use App\Models\User;

class QuizAttemptPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, QuizAttempt $quizAttempt): bool
    {
        return $quizAttempt->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, QuizAttempt $quizAttempt): bool
    {
        return $quizAttempt->user_id === $user->id;
    }

    public function delete(User $user, QuizAttempt $quizAttempt): bool
    {
        return false;
    }
}
