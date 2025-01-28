<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\ChampionshipCreated::class => [
            \App\Listeners\NotifyChampionshipCreated::class,
        ],
        \App\Events\ContentSubmitted::class => [
            \App\Listeners\ProcessContentSubmission::class,
        ],
        \App\Events\ContentApproved::class => [
            \App\Listeners\NotifyContentApproved::class,
        ],
        \App\Events\VoteSubmitted::class => [
            \App\Listeners\ProcessVoteSubmission::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
} 