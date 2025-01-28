<?php

namespace App\Listeners;

use App\Events\ChampionshipCreated;
use App\Notifications\NewChampionshipNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyChampionshipCreated implements ShouldQueue
{
    public function handle(ChampionshipCreated $event)
    {
        $users = User::whereHas('preferences', function ($query) {
            $query->where('notify_new_championships', true);
        })->get();

        foreach ($users as $user) {
            $user->notify(new NewChampionshipNotification($event->championship));
        }
    }
} 