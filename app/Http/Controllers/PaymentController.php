<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\PaymentGateway;
use App\Notifications\PaymentNotification;
use Illuminate\Support\Facades\Log;
use App\Models\ChampionshipParticipant;

class PaymentController extends Controller
{
    protected $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function checkout(Transaction $transaction)
    {
        try {
            if ($transaction->status !== 'pending') {
                return redirect()->route('championships.show', $transaction->championship)
                    ->with('error', 'Esta transação não está mais pendente.');
            }

            $payment = $this->paymentGateway->createPayment($transaction);

            return redirect($payment['url']);
        } catch (\Exception $e) {
            Log::error('Erro no checkout: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('championships.show', $transaction->championship)
                ->with('error', 'Erro ao processar pagamento. Por favor, tente novamente.');
        }
    }

    public function success(Transaction $transaction)
    {
        try {
            $paymentInfo = $this->paymentGateway->getPaymentInfo($transaction->payment_id);

            if ($paymentInfo['status'] === 'succeeded') {
                $transaction->complete();

                return redirect()->route('championships.show', $transaction->championship)
                    ->with('success', 'Pagamento confirmado com sucesso!');
            }

            return redirect()->route('championships.show', $transaction->championship)
                ->with('info', 'Aguardando confirmação do pagamento...');
        } catch (\Exception $e) {
            Log::error('Erro na confirmação: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('championships.show', $transaction->championship)
                ->with('error', 'Erro ao confirmar pagamento. Entre em contato com o suporte.');
        }
    }

    public function cancel(Transaction $transaction)
    {
        $transaction->fail('Cancelado pelo usuário');

        return redirect()->route('championships.show', $transaction->championship)
            ->with('info', 'Pagamento cancelado.');
    }

    public function show(Championship $championship)
    {
        if (!$championship->isRegistrationOpen()) {
            return redirect()->back()->with('error', 'As inscrições não estão abertas.');
        }

        $participant = ChampionshipParticipant::firstOrCreate([
            'championship_id' => $championship->id,
            'user_id' => auth()->id()
        ]);

        return view('payments.show', compact('championship', 'participant'));
    }

    public function process(Request $request, Championship $championship)
    {
        if (!$championship->isRegistrationOpen()) {
            return redirect()->back()->with('error', 'As inscrições não estão abertas.');
        }

        $participant = ChampionshipParticipant::where([
            'championship_id' => $championship->id,
            'user_id' => auth()->id()
        ])->firstOrFail();

        // Simula processamento do pagamento
        $paymentId = Str::random(10);

        $participant->update([
            'status' => 'paid',
            'payment_id' => $paymentId,
            'payment_date' => now()
        ]);

        return redirect()
            ->route('championships.show', $championship)
            ->with('success', 'Pagamento processado com sucesso! Você já pode participar do campeonato.');
    }

    public function webhook(Request $request)
    {
        // Lógica para processar webhooks de pagamento
        $paymentId = $request->input('payment_id');
        $status = $request->input('status');

        $participant = ChampionshipParticipant::where('payment_id', $paymentId)->first();

        if ($participant && $status === 'paid') {
            $participant->update([
                'status' => 'confirmed',
                'payment_date' => now()
            ]);
        }

        return response()->json(['success' => true]);
    }

    protected function verifyWebhookSignature(Request $request)
    {
        $signature = $request->header('X-Payment-Signature');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, config('services.payment.webhook_secret'));

        return hash_equals($expectedSignature, $signature);
    }
}
