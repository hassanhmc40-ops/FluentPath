<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WritingSubmission;

class WritingSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, WritingSubmission $writingSubmission): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, WritingSubmission $writingSubmission): bool
    {
        return false;
    }

    public function delete(User $user, WritingSubmission $writingSubmission): bool
    {
        return false;
    }
}
