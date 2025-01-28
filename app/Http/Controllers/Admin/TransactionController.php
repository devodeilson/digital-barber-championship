<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\PaymentGateway;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    protected $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'championship'])
            ->latest();

        // Filtros
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                })->orWhereHas('championship', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transactions = $query->paginate(15);

        // Estatísticas
        $totalAmount = Transaction::where('status', 'completed')->sum('amount');
        $successRate = Transaction::whereIn('status', ['completed', 'failed'])
            ->selectRaw('(COUNT(CASE WHEN status = "completed" THEN 1 END) / COUNT(*)) * 100 as rate')
            ->value('rate');
        $averageTicket = Transaction::where('status', 'completed')
            ->avg('amount');

        return view('admin.transactions.index', compact(
            'transactions',
            'totalAmount',
            'successRate',
            'averageTicket'
        ));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'championship', 'status_history']);
        
        // Buscar informações atualizadas do gateway
        if ($transaction->payment_id) {
            $gatewayInfo = $this->paymentGateway->getPaymentInfo($transaction->payment_id);
            $transaction->gateway_status = $gatewayInfo['status'];
        }

        return view('admin.transactions.show', compact('transaction'));
    }

    public function complete(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Esta transação não está pendente.');
        }

        $transaction->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // Registrar no histórico
        $transaction->status_history()->create([
            'status' => 'completed',
            'notes' => 'Marcado como concluído manualmente pelo administrador'
        ]);

        // Notificar usuário
        $transaction->user->notify(new TransactionCompleted($transaction));

        return back()->with('success', 'Transação marcada como concluída com sucesso.');
    }

    public function fail(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Esta transação não está pendente.');
        }

        $transaction->update([
            'status' => 'failed',
            'failed_at' => now()
        ]);

        // Registrar no histórico
        $transaction->status_history()->create([
            'status' => 'failed',
            'notes' => 'Marcado como falha manualmente pelo administrador'
        ]);

        // Notificar usuário
        $transaction->user->notify(new TransactionFailed($transaction));

        return back()->with('success', 'Transação marcada como falha com sucesso.');
    }

    public function refund(Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return back()->with('error', 'Apenas transações concluídas podem ser reembolsadas.');
        }

        try {
            // Processar reembolso no gateway
            $refund = $this->paymentGateway->refund($transaction->payment_id);

            $transaction->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'refund_id' => $refund['id']
            ]);

            // Registrar no histórico
            $transaction->status_history()->create([
                'status' => 'refunded',
                'notes' => 'Reembolso processado com sucesso'
            ]);

            // Notificar usuário
            $transaction->user->notify(new TransactionRefunded($transaction));

            return back()->with('success', 'Reembolso processado com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar reembolso: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new TransactionsExport($request), 'transactions.xlsx');
    }

    public function report(Request $request)
    {
        $startDate = $request->start_date ?? now()->subMonth();
        $endDate = $request->end_date ?? now();

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'championship'])
            ->get();

        $statistics = [
            'total_amount' => $transactions->where('status', 'completed')->sum('amount'),
            'total_count' => $transactions->count(),
            'success_rate' => $transactions->whereIn('status', ['completed', 'failed'])
                ->count() > 0 ? 
                ($transactions->where('status', 'completed')->count() / 
                 $transactions->whereIn('status', ['completed', 'failed'])->count()) * 100 : 0,
            'payment_methods' => $transactions->groupBy('payment_method')
                ->map(fn($group) => $group->count()),
            'daily_totals' => $transactions->where('status', 'completed')
                ->groupBy(fn($t) => $t->created_at->format('Y-m-d'))
                ->map(fn($group) => $group->sum('amount'))
        ];

        return view('admin.transactions.report', compact(
            'transactions',
            'statistics',
            'startDate',
            'endDate'
        ));
    }
} 