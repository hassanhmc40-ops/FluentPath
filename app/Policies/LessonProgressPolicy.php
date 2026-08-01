<?php

namespace App\Policies;

use App\Models\LessonProgress;
use App\Models\User;

class LessonProgressPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LessonProgress $lessonProgress): bool
    {
        return $lessonProgress->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, LessonProgress $lessonProgress): bool
    {
        return $lessonProgress->user_id === $user->id;
    }

    public function delete(User $user, LessonProgress $lessonProgress): bool
    {
        return false;
    }
}
