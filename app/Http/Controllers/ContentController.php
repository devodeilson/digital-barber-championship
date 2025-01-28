<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Championship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ContentController extends Controller
{
    public function showUploadForm(Championship $championship)
    {
        // Verifica se o usuário está inscrito no campeonato
        if (!$championship->participants()->where('user_id', auth()->id())->exists()) {
            return redirect()->route('championships.show', $championship)
                ->with('error', 'Você precisa estar inscrito para enviar conteúdo.');
        }

        // Calcula a etapa atual
        $currentStage = $this->calculateCurrentStage($championship);

        // Verifica se está no período de envio
        $canSubmit = $this->checkSubmissionPeriod($championship);

        return view('content.upload', compact('championship', 'currentStage', 'canSubmit'));
    }

    public function store(Request $request, Championship $championship)
    {
        $request->validate([
            'photos.*' => 'required|image|max:5120', // 5MB max
            'video_url' => 'nullable|url',
            'stage_number' => 'required|integer'
        ]);

        // Verifica período de envio
        if (!$this->checkSubmissionPeriod($championship)) {
            return back()->with('error', 'Fora do período de envio de conteúdo.');
        }

        // Processa e salva as fotos
        $photoUrls = [];
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('content_photos/' . auth()->id(), 'public');
            $photoUrls[] = $path;
        }

        Content::create([
            'user_id' => auth()->id(),
            'championship_id' => $championship->id,
            'stage_number' => $request->stage_number,
            'photos' => $photoUrls,
            'video_url' => $request->video_url,
            'submission_date' => now(),
            'status' => 'pending'
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Conteúdo enviado com sucesso!');
    }

    public function index()
    {
        $contents = Content::where('user_id', auth()->id())
            ->with('championship')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('content.index', compact('contents'));
    }

    private function calculateCurrentStage(Championship $championship)
    {
        $weeksSinceStart = Carbon::parse($championship->start_date)->diffInWeeks(now());
        return min($weeksSinceStart + 1, $championship->weekly_stages);
    }

    private function checkSubmissionPeriod(Championship $championship)
    {
        $now = Carbon::now();
        $stageDate = Carbon::parse($championship->start_date)
            ->addWeeks($this->calculateCurrentStage($championship) - 1);

        $submissionStart = $stageDate->copy()->subHours(18);
        $submissionEnd = $stageDate->copy()->setHour(18);

        return $now->between($submissionStart, $submissionEnd);
    }

    public function getVotingContent(Championship $championship)
    {
        // Verifica se está no período de votação (20:30 até 23:59 de domingo)
        $now = Carbon::now();
        $votingStart = Carbon::now()->setHour(20)->setMinute(30);
        $votingEnd = Carbon::now()->endOfWeek()->setHour(23)->setMinute(59);

        if ($now->lt($votingStart) || $now->gt($votingEnd)) {
            return response()->json(['error' => 'Fora do período de votação'], 422);
        }

        $contents = Content::with(['user:id,name,country'])
            ->where('championship_id', $championship->id)
            ->where('status', 'approved')
            ->orderBy('submission_date', 'desc')
            ->paginate(10);

        return response()->json($contents);
    }

    public function voting()
    {
        $contents = Content::with(['user', 'championship'])
            ->whereHas('championship', function ($query) {
                $query->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('contents.voting', compact('contents'));
    }
}
