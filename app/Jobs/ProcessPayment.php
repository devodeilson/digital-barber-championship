<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\PaymentGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\Loggable;
use App\Services\Logger;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Loggable;

    protected $transaction;
    public $tries = 3;
    public $backoff = 60;
    protected $logger;

    public function __construct(Transaction $transaction, Logger $logger)
    {
        $this->transaction = $transaction;
        $this->logger = $logger;
    }

    public function handle(PaymentGateway $gateway)
    {
        try {
            $paymentInfo = $gateway->getPaymentInfo($this->transaction->payment_id);

            if ($paymentInfo['status'] === 'succeeded' && $this->transaction->status === 'pending') {
                $this->transaction->complete();
            } elseif ($paymentInfo['status'] === 'failed' && $this->transaction->status === 'pending') {
                $this->transaction->fail($paymentInfo['failure_reason']);
            }

            $this->logger->payment('processed', $this->transaction, [
                'gateway_response' => $paymentInfo
            ]);
        } catch (\Exception $e) {
            $this->logger->error($e, [
                'transaction_id' => $this->transaction->id
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->transaction->fail('Erro ao processar pagamento após várias tentativas');
            }

            throw $e;
        }
    }
} 