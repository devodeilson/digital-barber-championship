<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Http\Requests\VoteRequest;

class VoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Vote::with(['user', 'content.championship'])
            ->latest();

        // Filtros
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                })->orWhereHas('content.championship', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                });
            });
        }

        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        $votes = $query->paginate(15);

        return view('admin.votes.index', compact('votes'));
    }

    public function show(Vote $vote)
    {
        $vote->load(['user', 'content.championship']);
        return view('admin.votes.show', compact('vote'));
    }

    public function destroy(Vote $vote)
    {
        // Recalcular média do conteúdo
        $content = $vote->content;
        
        $vote->delete();

        $content->updateRating();

        return redirect()
            ->route('admin.votes.index')
            ->with('success', 'Voto excluído com sucesso.');
    }

    public function report(Request $request)
    {
        $startDate = $request->start_date ?? now()->subMonth();
        $endDate = $request->end_date ?? now();

        $votes = Vote::whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'content.championship'])
            ->get();

        $statistics = [
            'total_votes' => $votes->count(),
            'average_rating' => $votes->avg('rating'),
            'rating_distribution' => $votes->groupBy('rating')
                ->map(fn($group) => $group->count()),
            'top_voters' => $votes->groupBy('user_id')
                ->map(fn($group) => [
                    'user' => $group->first()->user,
                    'count' => $group->count()
                ])
                ->sortByDesc('count')
                ->take(10)
        ];

        return view('admin.votes.report', compact('votes', 'statistics', 'startDate', 'endDate'));
    }
} 