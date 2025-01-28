<?php

namespace App\Events;

use App\Models\Championship;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChampionshipCreated
{
    use Dispatchable, SerializesModels;

    public $championship;

    public function __construct(Championship $championship)
    {
        $this->championship = $championship;
    }
} 