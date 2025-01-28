<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransactionExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $status;

    public function __construct($startDate = null, $endDate = null, $status = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
    }

    public function query()
    {
        $query = Transaction::query()
            ->with(['user', 'championship'])
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            });

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Usuário',
            'Campeonato',
            'Valor',
            'Status',
            'Método',
            'Data',
            'Conclusão',
            'Gateway ID'
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->user->name,
            $transaction->championship->name,
            'R$ ' . number_format($transaction->amount, 2, ',', '.'),
            $transaction->status,
            $transaction->payment_method,
            $transaction->created_at->format('d/m/Y H:i'),
            $transaction->completed_at?->format('d/m/Y H:i') ?? 'N/A',
            $transaction->payment_id
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:I1' => ['fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CCCCCC']
            ]],
        ];
    }
} 