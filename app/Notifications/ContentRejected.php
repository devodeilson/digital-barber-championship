<?php

namespace App\Notifications;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ContentRejected extends Notification
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
            ->subject('Conteúdo Não Aprovado')
            ->greeting('Olá ' . $notifiable->name)
            ->line('Infelizmente seu conteúdo para o campeonato ' . $this->content->championship->name . ' não foi aprovado.')
            ->line('Motivo: ' . ($this->content->rejection_reason ?? 'Não atende aos critérios do campeonato'))
            ->line('Você pode enviar um novo conteúdo dentro do prazo do campeonato.')
            ->action('Ver Campeonato', route('championships.show', $this->content->championship))
            ->line('Se tiver dúvidas, entre em contato com nosso suporte.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'content_id' => $this->content->id,
            'championship_id' => $this->content->championship_id,
            'message' => 'Seu conteúdo não foi aprovado.'
        ];
    }
} 