<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Championship;

class ChampionshipNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $championship;
    public $type;
    public $message;

    public function __construct(Championship $championship, string $type, string $message)
    {
        $this->championship = $championship;
        $this->type = $type;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Atualização do Campeonato: {$this->championship->name}")
            ->greeting("Olá {$notifiable->name}!")
            ->line($this->message)
            ->action('Ver Campeonato', route('championships.show', $this->championship))
            ->line('Obrigado por participar!');
    }

    public function toArray($notifiable): array
    {
        return [
            'championship_id' => $this->championship->id,
            'championship_name' => $this->championship->name,
            'type' => $this->type,
            'message' => $this->message
        ];
    }
} 