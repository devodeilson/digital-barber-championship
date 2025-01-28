<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Championship;
use App\Models\Content;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function generate(Request $request)
    {
        $type = $request->type;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        switch ($type) {
            case 'users':
                $data = $this->getUsersReport($startDate, $endDate);
                break;
            case 'championships':
                $data = $this->getChampionshipsReport($startDate, $endDate);
                break;
            case 'revenue':
                $data = $this->getRevenueReport($startDate, $endDate);
                break;
            default:
                return back()->with('error', 'Tipo de relatório inválido');
        }

        return view('admin.reports.show', compact('data', 'type', 'startDate', 'endDate'));
    }

    private function getUsersReport($startDate, $endDate)
    {
        return User::whereBetween('created_at', [$startDate, $endDate])
            ->withCount('championships')
            ->withCount('contents')
            ->get();
    }

    private function getChampionshipsReport($startDate, $endDate)
    {
        return Championship::whereBetween('created_at', [$startDate, $endDate])
            ->withCount('contents')
            ->withSum('transactions', 'amount')
            ->get();
    }

    private function getRevenueReport($startDate, $endDate)
    {
        return Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->with('championship')
            ->get()
            ->groupBy(function($transaction) {
                return $transaction->created_at->format('Y-m');
            });
    }

    public function export(Request $request, $type)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonth());
        $endDate = $request->input('end_date', Carbon::now());

        switch ($type) {
            case 'users':
                return $this->exportUsers($startDate, $endDate);
            case 'championships':
                return $this->exportChampionships($startDate, $endDate);
            case 'contents':
                return $this->exportContents($startDate, $endDate);
            case 'transactions':
                return $this->exportTransactions($startDate, $endDate);
            default:
                return redirect()->back()->with('error', 'Tipo de relatório inválido.');
        }
    }

    private function exportUsers($startDate, $endDate)
    {
        $users = User::whereBetween('created_at', [$startDate, $endDate])
            ->get(['name', 'email', 'created_at']);

        // Lógica para exportar CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=users.csv',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nome', 'Email', 'Data de Cadastro']);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->name,
                    $user->email,
                    $user->created_at->format('d/m/Y H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Métodos similares para outros tipos de exportação...
}
