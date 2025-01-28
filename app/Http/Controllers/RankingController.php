<?php

namespace App\Http\Controllers;

use App\Models\Ranking;
use App\Models\Championship;
use App\Models\Content;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    public function show(Championship $championship, $type = 'monthly')
    {
        $rankings = Ranking::where('championship_id', $championship->id)
            ->where('type', $type)
            ->with(['user:id,name,country,profile_photo'])
            ->orderBy('position')
            ->paginate(20);

        // Busca posição do usuário atual
        $userRanking = Ranking::where('championship_id', $championship->id)
            ->where('type', $type)
            ->where('user_id', auth()->id())
            ->first();

        return view('rankings.show', compact('championship', 'rankings', 'userRanking', 'type'));
    }

    public function updateStageRanking(Championship $championship, $stageNumber)
    {
        // Calcula ranking baseado nas médias dos conteúdos da etapa
        $results = Content::where('championship_id', $championship->id)
            ->where('stage_number', $stageNumber)
            ->where('status', 'approved')
            ->select('user_id', DB::raw('AVG(average_score) as points'))
            ->groupBy('user_id')
            ->orderByDesc('points')
            ->get();

        $position = 1;
        foreach ($results as $result) {
            Ranking::updateOrCreate(
                [
                    'championship_id' => $championship->id,
                    'user_id' => $result->user_id,
                    'type' => 'stage',
                    'stage_number' => $stageNumber
                ],
                [
                    'points' => $result->points,
                    'position' => $position,
                    'virtual_coins' => $this->calculateVirtualCoins($position)
                ]
            );
            $position++;
        }

        $this->updateMonthlyRanking($championship);
        return redirect()->back()->with('success', 'Rankings atualizados com sucesso!');
    }

    public function updateMonthlyRanking(Championship $championship)
    {
        // Calcula ranking mensal baseado na média das etapas
        $results = Ranking::where('championship_id', $championship->id)
            ->where('type', 'stage')
            ->select('user_id', DB::raw('AVG(points) as monthly_points'))
            ->groupBy('user_id')
            ->orderByDesc('monthly_points')
            ->get();

        $position = 1;
        foreach ($results as $result) {
            Ranking::updateOrCreate(
                [
                    'championship_id' => $championship->id,
                    'user_id' => $result->user_id,
                    'type' => 'monthly'
                ],
                [
                    'points' => $result->monthly_points,
                    'position' => $position,
                    'virtual_coins' => $this->calculateVirtualCoins($position)
                ]
            );
            $position++;
        }
    }

    public function getChampionshipRanking(Championship $championship, $type = 'monthly')
    {
        $rankings = Ranking::with('user:id,name,country')
            ->where('championship_id', $championship->id)
            ->where('type', $type)
            ->orderBy('position')
            ->get();

        return response()->json($rankings);
    }

    private function calculateVirtualCoins($position)
    {
        return match (true) {
            $position === 1 => 100,
            $position === 2 => 75,
            $position === 3 => 50,
            $position <= 10 => 25,
            default => 10
        };
    }

    public function index(Championship $championship)
    {
        $dailyRanking = Ranking::with('user')
            ->where('championship_id', $championship->id)
            ->where('type', 'daily')
            ->where('ranking_date', now()->toDateString())
            ->orderBy('position')
            ->take(10)
            ->get();

        $generalRanking = Ranking::with('user')
            ->where('championship_id', $championship->id)
            ->where('type', 'general')
            ->orderBy('position')
            ->take(10)
            ->get();

        return view('rankings.index', compact('championship', 'dailyRanking', 'generalRanking'));
    }

    public function updateRankings(Championship $championship)
    {
        // Atualiza ranking diário
        $dailyScores = Video::where('championship_id', $championship->id)
            ->where('status', 'published')
            ->select('user_id', DB::raw('AVG(rating) as score'))
            ->join('votes', 'videos.id', '=', 'votes.video_id')
            ->where('votes.created_at', '>=', now()->startOfDay())
            ->groupBy('user_id')
            ->orderByDesc('score')
            ->get();

        foreach ($dailyScores as $position => $score) {
            Ranking::updateOrCreate(
                [
                    'championship_id' => $championship->id,
                    'user_id' => $score->user_id,
                    'type' => 'daily',
                    'ranking_date' => now()->toDateString()
                ],
                [
                    'position' => $position + 1,
                    'score' => $score->score
                ]
            );
        }

        // Atualiza ranking geral
        $generalScores = Video::where('championship_id', $championship->id)
            ->where('status', 'published')
            ->select('user_id', DB::raw('AVG(rating) as score'))
            ->join('votes', 'videos.id', '=', 'votes.video_id')
            ->groupBy('user_id')
            ->orderByDesc('score')
            ->get();

        foreach ($generalScores as $position => $score) {
            Ranking::updateOrCreate(
                [
                    'championship_id' => $championship->id,
                    'user_id' => $score->user_id,
                    'type' => 'general'
                ],
                [
                    'position' => $position + 1,
                    'score' => $score->score,
                    'ranking_date' => now()->toDateString()
                ]
            );
        }

        // Se o campeonato terminou, atualiza os finalistas
        if ($championship->voting_end <= now() && $championship->status === 'voting') {
            $finalists = Ranking::where('championship_id', $championship->id)
                ->where('type', 'general')
                ->orderBy('position')
                ->take(4)
                ->get();

            foreach ($finalists as $position => $finalist) {
                Ranking::create([
                    'championship_id' => $championship->id,
                    'user_id' => $finalist->user_id,
                    'position' => $position + 1,
                    'score' => $finalist->score,
                    'type' => 'final',
                    'ranking_date' => now()->toDateString()
                ]);
            }

            $championship->update(['status' => 'finished']);
        }

        return redirect()->back()->with('success', 'Rankings atualizados com sucesso!');
    }
}
