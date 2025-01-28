<?php

namespace App\Notifications;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ContentApproved extends Notification
{
    use Queueable;

    protected $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Seu conteúdo foi aprovado!')
            ->greeting('Olá ' . $notifiable->name)
            ->line('Seu conteúdo para o campeonato ' . $this->content->championship->name . ' foi aprovado!')
            ->line('Agora ele está disponível para votação dos outros participantes.')
            ->action('Ver Conteúdo', route('contents.show', $this->content))
            ->line('Continue participando!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'content_id' => $this->content->id,
            'championship_id' => $this->content->championship_id,
            'message' => 'Seu conteúdo foi aprovado!'
        ];
    }
} 