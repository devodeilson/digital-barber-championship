<?php

namespace App\Services;

use App\Models\Transaction;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Exception;

class PaymentGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Transaction $transaction)
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $transaction->amount * 100, // Stripe usa centavos
                'currency' => 'brl',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'transaction_id' => $transaction->id,
                    'user_id' => $transaction->user_id,
                    'championship_id' => $transaction->championship_id
                ]
            ]);

            $transaction->update([
                'payment_intent_id' => $paymentIntent->id,
                'payment_intent_client_secret' => $paymentIntent->client_secret
            ]);

            return [
                'clientSecret' => $paymentIntent->client_secret,
                'publicKey' => config('services.stripe.key')
            ];
        } catch (ApiErrorException $e) {
            \Log::error('Stripe API Error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);

            throw new Exception('Payment processing failed: ' . $e->getMessage());
        }
    }

    public function confirmPayment(Transaction $transaction)
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($transaction->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);

                return true;
            }

            return false;
        } catch (ApiErrorException $e) {
            \Log::error('Payment confirmation failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);

            throw new Exception('Payment confirmation failed: ' . $e->getMessage());
        }
    }

    public function handleWebhook(array $payload)
    {
        try {
            $event = \Stripe\Event::constructFrom($payload);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $transaction = Transaction::where('payment_intent_id', $paymentIntent->id)->first();

                    if ($transaction) {
                        $transaction->update([
                            'status' => 'completed',
                            'completed_at' => now()
                        ]);
                    }
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    $transaction = Transaction::where('payment_intent_id', $paymentIntent->id)->first();

                    if ($transaction) {
                        $transaction->update([
                            'status' => 'failed',
                            'error_message' => $paymentIntent->last_payment_error->message ?? 'Payment failed'
                        ]);
                    }
                    break;
            }

            return true;
        } catch (Exception $e) {
            \Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            throw $e;
        }
    }

    public function processPayment($amount, $paymentMethod, $data)
    {
        try {
            switch ($paymentMethod) {
                case 'credit_card':
                    return $this->processCreditCard($amount, $data);
                case 'pix':
                    return $this->processPix($amount);
                case 'bank_slip':
                    return $this->processBankSlip($amount);
                default:
                    throw new Exception('Método de pagamento não suportado');
            }
        } catch (Exception $e) {
            throw new Exception('Erro ao processar pagamento: ' . $e->getMessage());
        }
    }

    private function processCreditCard($amount, $data)
    {
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount * 100, // Stripe usa centavos
            'currency' => 'brl',
            'payment_method_types' => ['card'],
            'description' => 'Inscrição em Campeonato',
        ]);

        return [
            'id' => $paymentIntent->id,
            'status' => $paymentIntent->status === 'succeeded' ? 'completed' : 'pending',
            'client_secret' => $paymentIntent->client_secret,
        ];
    }

    private function processPix($amount)
    {
        // Implementar integração com PIX
        return [
            'id' => 'PIX_' . uniqid(),
            'status' => 'pending',
            'qr_code' => 'QR_CODE_URL',
            'expiration' => now()->addHours(24),
        ];
    }

    private function processBankSlip($amount)
    {
        // Implementar integração com boleto
        return [
            'id' => 'BOLETO_' . uniqid(),
            'status' => 'pending',
            'pdf_url' => 'BOLETO_PDF_URL',
            'expiration' => now()->addDays(3),
        ];
    }
}
