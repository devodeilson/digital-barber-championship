<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Spatie\Backup\Notifications\BaseNotification;
use Throwable;

class BackupHasFailed extends BaseNotification
{
    public function __construct(
        public string $error,
        public ?Throwable $throwable = null
    ) {
        parent::__construct();
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('FALHA NO BACKUP: ' . $this->applicationName())
            ->greeting('Atenção!')
            ->line('Houve uma falha ao realizar o backup do sistema.')
            ->line('Detalhes do erro:')
            ->line($this->error)
            ->when($this->throwable !== null, function ($mail) {
                return $mail->line('Stack trace:')
                    ->line($this->throwable->getTraceAsString());
            })
            ->line('Por favor, verifique o sistema de backup com urgência.');
    }
} 