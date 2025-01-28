<?php

namespace App\Events;

use App\Models\Vote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteSubmitted
{
    use Dispatchable, SerializesModels;

    public $vote;

    public function __construct(Vote $vote)
    {
        $this->vote = $vote;
    }
} 