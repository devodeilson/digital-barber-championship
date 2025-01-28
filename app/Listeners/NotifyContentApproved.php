<?php

namespace App\Listeners;

use App\Events\ContentApproved;
use App\Notifications\ContentApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyContentApproved implements ShouldQueue
{
    public function handle(ContentApproved $event)
    {
        $content = $event->content;
        $user = $content->user;
        
        $user->notify(new ContentApprovedNotification($content));

        // Notifica participantes do campeonato
        $championship = $content->championship;
        foreach ($championship->participants as $participant) {
            if ($participant->id !== $user->id) {
                $participant->notify(new NewContentAvailableNotification($content));
            }
        }
    }
} 