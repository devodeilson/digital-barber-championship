<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Championship;
use Illuminate\Http\Request;
use App\Services\PaymentGateway;

class TransactionController extends Controller
{
    protected $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function index()
    {
        $transactions = auth()->user()
            ->transactions()
            ->with(['championship'])
            ->latest()
            ->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    public function store(Request $request, Championship $championship)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:credit_card,pix,bank_slip',
            'card_number' => 'required_if:payment_method,credit_card',
            'card_expiry' => 'required_if:payment_method,credit_card',
            'card_cvv' => 'required_if:payment_method,credit_card',
        ]);

        try {
            $payment = $this->paymentGateway->processPayment(
                $championship->entry_fee,
                $validated['payment_method'],
                $request->all()
            );

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'championship_id' => $championship->id,
                'amount' => $championship->entry_fee,
                'payment_method' => $validated['payment_method'],
                'payment_id' => $payment['id'],
                'status' => $payment['status']
            ]);

            if ($payment['status'] === 'completed') {
                $championship->participants()->attach(auth()->id());
            }

            return redirect()
                ->route('championships.show', $championship)
                ->with('success', 'Pagamento processado com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return view('transactions.show', compact('transaction'));
    }
}
