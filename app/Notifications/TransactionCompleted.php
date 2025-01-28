<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TransactionCompleted extends Notification
{
    use Queueable;

    protected $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Pagamento Confirmado!')
            ->greeting('Olá ' . $notifiable->name)
            ->line('Seu pagamento foi confirmado com sucesso!')
            ->line('Detalhes da transação:')
            ->line('- Campeonato: ' . $this->transaction->championship->name)
            ->line('- Valor: R$ ' . number_format($this->transaction->amount, 2, ',', '.'))
            ->line('- Data: ' . $this->transaction->completed_at->format('d/m/Y H:i'))
            ->action('Ver Campeonato', route('championships.show', $this->transaction->championship))
            ->line('Obrigado por participar!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'transaction_id' => $this->transaction->id,
            'championship_id' => $this->transaction->championship_id,
            'amount' => $this->transaction->amount,
            'message' => 'Seu pagamento foi confirmado com sucesso!'
        ];
    }
} 