<?php

namespace App\Notifications;

use App\Models\Championship;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ChampionshipStarted extends Notification
{
    use Queueable;

    protected $championship;

    public function __construct(Championship $championship)
    {
        $this->championship = $championship;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('O Campeonato Começou!')
            ->greeting('Olá ' . $notifiable->name)
            ->line('O campeonato ' . $this->championship->name . ' começou!')
            ->line('Você já pode enviar seus conteúdos e participar das votações.')
            ->line('Data de término: ' . $this->championship->end_date->format('d/m/Y'))
            ->action('Participar Agora', route('championships.show', $this->championship))
            ->line('Boa sorte!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'championship_id' => $this->championship->id,
            'message' => 'O campeonato ' . $this->championship->name . ' começou!'
        ];
    }
} 