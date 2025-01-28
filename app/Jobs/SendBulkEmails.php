<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChampionshipAnnouncement;

class SendBulkEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;
    protected $championship;
    public $tries = 3;
    public $timeout = 600;

    public function __construct(Collection $users, $championship)
    {
        $this->users = $users;
        $this->championship = $championship;
    }

    public function handle()
    {
        $this->users->each(function ($user) {
            Mail::to($user)
                ->queue(new ChampionshipAnnouncement($this->championship));
        });
    }
} 