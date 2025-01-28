<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TransactionFailed extends Notification
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
            ->subject('Falha no Pagamento')
            ->greeting('Olá ' . $notifiable->name)
            ->line('Infelizmente houve uma falha no processamento do seu pagamento.')
            ->line('Detalhes da transação:')
            ->line('- Campeonato: ' . $this->transaction->championship->name)
            ->line('- Valor: R$ ' . number_format($this->transaction->amount, 2, ',', '.'))
            ->line('- Motivo: ' . ($this->transaction->notes ?? 'Erro no processamento'))
            ->action('Tentar Novamente', route('championships.show', $this->transaction->championship))
            ->line('Se precisar de ajuda, entre em contato com nosso suporte.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'transaction_id' => $this->transaction->id,
            'championship_id' => $this->transaction->championship_id,
            'amount' => $this->transaction->amount,
            'message' => 'Houve uma falha no processamento do seu pagamento.'
        ];
    }
} 