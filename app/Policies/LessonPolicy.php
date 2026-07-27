<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Lesson $lesson): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return false;
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return false;
    }
}
