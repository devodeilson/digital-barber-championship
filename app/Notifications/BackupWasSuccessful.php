<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Spatie\Backup\Notifications\BaseNotification;

class BackupWasSuccessful extends BaseNotification
{
    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Backup Realizado com Sucesso: ' . $this->applicationName())
            ->greeting('Olá!')
            ->line('O backup do sistema foi realizado com sucesso.')
            ->line('Detalhes do backup:')
            ->line('- Data: ' . now()->format('d/m/Y H:i:s'))
            ->line('- Tamanho: ' . $this->getBackupSize())
            ->line('- Destinos: ' . implode(', ', config('backup.backup.destination.disks')))
            ->line('O backup está seguro e disponível para restauração se necessário.');
    }
} 