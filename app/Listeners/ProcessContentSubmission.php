<?php

namespace App\Listeners;

use App\Events\ContentSubmitted;
use App\Jobs\ProcessContentMedia;
use App\Notifications\ContentSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessContentSubmission implements ShouldQueue
{
    public function handle(ContentSubmitted $event)
    {
        // Processa a mídia do conteúdo
        ProcessContentMedia::dispatch($event->content);

        // Notifica os administradores
        $admins = \App\Models\User::where('is_admin', true)->get();
        foreach ($admins as $admin) {
            $admin->notify(new ContentSubmittedNotification($event->content));
        }
    }
} 