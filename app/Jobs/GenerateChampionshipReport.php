<?php

namespace App\Jobs;

use App\Models\Championship;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PDF;

class GenerateChampionshipReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $championship;
    protected $email;

    public function __construct(Championship $championship, string $email)
    {
        $this->championship = $championship;
        $this->email = $email;
    }

    public function handle(EmailService $emailService)
    {
        // Gerar relatório em PDF
        $pdf = PDF::loadView('reports.championship', [
            'championship' => $this->championship,
            'participants' => $this->championship->participants,
            'contents' => $this->championship->contents,
            'statistics' => $this->generateStatistics()
        ]);

        // Salvar PDF
        $filename = "championship_{$this->championship->id}_report.pdf";
        $pdf->save(storage_path("app/reports/{$filename}"));

        // Enviar email com o relatório
        $emailService->sendChampionshipReport($this->email, $filename);
    }

    protected function generateStatistics()
    {
        return [
            'total_participants' => $this->championship->participants()->count(),
            'total_contents' => $this->championship->contents()->count(),
            'average_rating' => $this->championship->contents()->avg('average_rating'),
            'total_votes' => $this->championship->contents()
                ->withCount('votes')
                ->get()
                ->sum('votes_count'),
            'top_rated_contents' => $this->championship->contents()
                ->orderBy('average_rating', 'desc')
                ->take(5)
                ->get()
        ];
    }
} 