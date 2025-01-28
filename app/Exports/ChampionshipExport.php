<?php

namespace App\Exports;

use App\Models\Championship;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ChampionshipExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $championship;
    protected $startDate;
    protected $endDate;

    public function __construct(Championship $championship, $startDate = null, $endDate = null)
    {
        $this->championship = $championship;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = $this->championship->participants()
            ->withCount(['contents' => function ($query) {
                if ($this->startDate && $this->endDate) {
                    $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
            }])
            ->withAvg(['contents' => function ($query) {
                if ($this->startDate && $this->endDate) {
                    $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
            }], 'average_rating');

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'Email',
            'Data de Inscrição',
            'Conteúdos Enviados',
            'Média de Avaliação',
            'Status',
            'Última Atividade'
        ];
    }

    public function map($participant): array
    {
        return [
            $participant->id,
            $participant->name,
            $participant->email,
            $participant->pivot->created_at->format('d/m/Y H:i'),
            $participant->contents_count,
            number_format($participant->contents_avg_average_rating ?? 0, 2),
            $participant->status,
            $participant->last_activity_at?->format('d/m/Y H:i') ?? 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:H1' => ['fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CCCCCC']
            ]],
        ];
    }
} 