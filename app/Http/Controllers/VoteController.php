<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Content;
use App\Models\Championship;
use App\Models\Submission;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Video;

class VoteController extends Controller
{
    public function showVotingPage(Championship $championship)
    {
        // Verifica se está no período de votação (20:30 até 23:59 de domingo)
        if (!$this->checkVotingPeriod()) {
            return back()->with('error', 'Fora do período de votação.');
        }

        // Busca conteúdos que ainda não foram votados pelo usuário
        $contents = Content::where('championship_id', $championship->id)
            ->where('status', 'approved')
            ->whereDoesntHave('votes', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['user:id,name,country'])
            ->paginate(1);

        // Busca estatísticas de votação do usuário
        $votingStats = [
            'total_votes' => Vote::where('user_id', auth()->id())
                ->where('created_at', '>=', Carbon::now()->startOfDay())
                ->count(),
            'remaining_contents' => $contents->total()
        ];

        return view('voting.index', compact('championship', 'contents', 'votingStats'));
    }

    public function store(Request $request, Video $video)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5'
        ]);

        if (!$video->championship->isVotingOpen()) {
            return response()->json([
                'error' => 'A votação não está aberta.'
            ], 403);
        }

        Vote::updateOrCreate(
            [
                'video_id' => $video->id,
                'user_id' => auth()->id()
            ],
            [
                'rating' => $validated['rating']
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Voto registrado com sucesso!',
            'newAverage' => $video->fresh()->getAverageRating(),
            'totalVotes' => $video->fresh()->getTotalVotes()
        ]);
    }

    public function destroy(Vote $vote)
    {
        $this->authorize('delete', $vote);

        DB::transaction(function () use ($vote) {
            $submission = $vote->submission;

            // Remove o voto
            $vote->delete();

            // Recalcula as estatísticas da submissão
            $submission->total_votes = $submission->total_votes - 1;
            $submission->average_rating = Vote::where('submission_id', $submission->id)
                ->avg('rating') ?? 0;
            $submission->save();
        });

        return back()->with('success', 'Voto removido com sucesso!');
    }

    private function checkVotingPeriod()
    {
        $now = Carbon::now();
        $votingStart = Carbon::now()->setHour(20)->setMinute(30);
        $votingEnd = Carbon::now()->endOfWeek()->setHour(23)->setMinute(59);

        return $now->between($votingStart, $votingEnd);
    }

    private function updateContentScore(Content $content)
    {
        $averageScore = Vote::where('content_id', $content->id)->avg('score');
        $content->update(['average_score' => $averageScore]);
    }

    public function getContentStats(Content $content)
    {
        $stats = [
            'total_votes' => Vote::where('content_id', $content->id)->count(),
            'average_score' => $content->average_score,
            'score_distribution' => Vote::where('content_id', $content->id)
                ->selectRaw('score, COUNT(*) as count')
                ->groupBy('score')
                ->get()
        ];

        return response()->json($stats);
    }

    public function index(Championship $championship)
    {
        if (!$championship->isVotingOpen()) {
            return redirect()->back()->with('error', 'A votação não está aberta.');
        }

        $videos = Video::with(['user', 'category', 'votes'])
            ->where('championship_id', $championship->id)
            ->where('status', 'published')
            ->paginate(12);

        $userVotes = Vote::where('user_id', auth()->id())
            ->whereIn('video_id', $videos->pluck('id'))
            ->pluck('rating', 'video_id')
            ->toArray();

        return view('votes.index', compact('championship', 'videos', 'userVotes'));
    }
}
