<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vote;

class VotePolicy
{
    public function delete(User $user, Vote $vote)
    {
        return $user->id === $vote->user_id;
    }
}
