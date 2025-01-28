<?php

namespace App\Listeners;

use App\Events\VoteSubmitted;
use App\Notifications\NewVoteNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessVoteSubmission implements ShouldQueue
{
    public function handle(VoteSubmitted $event)
    {
        $vote = $event->vote;
        $content = $vote->content;
        
        // Atualiza a média de votos do conteúdo
        $content->updateAverageRating();

        // Notifica o criador do conteúdo
        $content->user->notify(new NewVoteNotification($vote));

        // Verifica se o conteúdo atingiu um marco de votos
        if ($content->votes()->count() % 10 === 0) {
            $content->user->notify(new VoteMilestoneNotification($content));
        }
    }
} 