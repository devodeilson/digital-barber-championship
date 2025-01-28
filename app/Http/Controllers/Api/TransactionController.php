<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Models\Championship;
use App\Services\PaymentGateway;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;

class TransactionController extends ApiController
{
    protected $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function index(Request $request)
    {
        $transactions = Transaction::query()
            ->with(['user', 'championship'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->championship_id, function ($query, $championshipId) {
                return $query->where('championship_id', $championshipId);
            })
            ->when(!auth()->user()->isAdmin(), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return TransactionResource::collection($transactions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'championship_id' => 'required|exists:championships,id',
            'payment_method' => 'required|in:credit_card,pix,boleto'
        ]);

        $championship = Championship::findOrFail($request->championship_id);

        if (!$championship->isActive()) {
            return $this->respondError('Championship is not active');
        }

        if ($championship->hasParticipant(auth()->id())) {
            return $this->respondError('You are already a participant');
        }

        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'championship_id' => $championship->id,
            'amount' => $championship->entry_fee,
            'payment_method' => $request->payment_method,
            'status' => 'pending'
        ]);

        $paymentIntent = $this->paymentGateway->createPaymentIntent($transaction);

        return $this->respondCreated([
            'transaction' => new TransactionResource($transaction),
            'payment_intent' => $paymentIntent
        ]);
    }

    public function show(Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && $transaction->user_id !== auth()->id()) {
            return $this->respondForbidden();
        }

        return $this->respondSuccess(
            new TransactionResource($transaction->load(['user', 'championship']))
        );
    }

    public function confirm(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return $this->respondError('Transaction is not pending');
        }

        try {
            $this->paymentGateway->confirmPayment($transaction);
            
            return $this->respondSuccess(
                new TransactionResource($transaction->fresh())
            );
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function cancel(Transaction $transaction)
    {
        if (!in_array($transaction->status, ['pending', 'failed'])) {
            return $this->respondError('Transaction cannot be cancelled');
        }

        try {
            $this->paymentGateway->cancelPayment($transaction);
            
            return $this->respondSuccess(
                new TransactionResource($transaction->fresh())
            );
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function webhook(Request $request)
    {
        try {
            $this->paymentGateway->handleWebhook($request->all());
            return $this->respondSuccess();
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
} 