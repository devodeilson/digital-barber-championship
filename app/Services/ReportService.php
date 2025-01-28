<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Content;
use App\Models\Transaction;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    public function getChampionshipStats(Championship $championship)
    {
        $participants = $championship->participants()->count();
        $contents = $championship->contents()->count();
        $approvedContents = $championship->contents()->approved()->count();
        $totalVotes = Vote::whereHas('content', function ($query) use ($championship) {
            $query->where('championship_id', $championship->id);
        })->count();

        $revenue = Transaction::where('championship_id', $championship->id)
            ->where('status', 'completed')
            ->sum('amount');

        $topContents = $championship->contents()
            ->approved()
            ->orderByDesc('average_rating')
            ->take(5)
            ->get();

        $participationByDay = $championship->contents()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');

        $votesByDay = Vote::whereHas('content', function ($query) use ($championship) {
            $query->where('championship_id', $championship->id);
        })
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
        ->groupBy('date')
        ->get()
        ->pluck('count', 'date');

        return [
            'summary' => [
                'participants' => $participants,
                'contents' => $contents,
                'approved_contents' => $approvedContents,
                'total_votes' => $totalVotes,
                'revenue' => $revenue
            ],
            'top_contents' => $topContents,
            'participation_by_day' => $participationByDay,
            'votes_by_day' => $votesByDay
        ];
    }

    public function getFinancialReport($startDate = null, $endDate = null)
    {
        $query = Transaction::with(['user', 'championship'])
            ->where('status', 'completed');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transactions = $query->get();

        $totalRevenue = $transactions->sum('amount');
        $averageTransaction = $transactions->avg('amount');
        
        $revenueByChampionship = $transactions
            ->groupBy('championship_id')
            ->map(function ($group) {
                return [
                    'championship' => $group->first()->championship->name,
                    'total' => $group->sum('amount'),
                    'count' => $group->count()
                ];
            });

        $revenueByDay = $transactions
            ->groupBy(function ($transaction) {
                return $transaction->created_at->format('Y-m-d');
            })
            ->map(function ($group) {
                return $group->sum('amount');
            });

        $paymentMethods = $transactions
            ->groupBy('payment_method')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount')
                ];
            });

        return [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'average_transaction' => $averageTransaction,
                'total_transactions' => $transactions->count()
            ],
            'revenue_by_championship' => $revenueByChampionship,
            'revenue_by_day' => $revenueByDay,
            'payment_methods' => $paymentMethods
        ];
    }

    public function getUserEngagementReport($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);

        $activeUsers = DB::table('users')
            ->where('last_activity_at', '>=', $startDate)
            ->count();

        $newUsers = DB::table('users')
            ->where('created_at', '>=', $startDate)
            ->count();

        $contentCreation = Content::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');

        $votingActivity = Vote::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');

        $topContributors = Content::where('created_at', '>=', $startDate)
            ->select('user_id', DB::raw('COUNT(*) as content_count'))
            ->groupBy('user_id')
            ->orderByDesc('content_count')
            ->take(10)
            ->with('user')
            ->get();

        $mostActiveVoters = Vote::where('created_at', '>=', $startDate)
            ->select('user_id', DB::raw('COUNT(*) as vote_count'))
            ->groupBy('user_id')
            ->orderByDesc('vote_count')
            ->take(10)
            ->with('user')
            ->get();

        return [
            'summary' => [
                'active_users' => $activeUsers,
                'new_users' => $newUsers,
                'total_content' => $contentCreation->sum(),
                'total_votes' => $votingActivity->sum()
            ],
            'content_creation' => $contentCreation,
            'voting_activity' => $votingActivity,
            'top_contributors' => $topContributors,
            'most_active_voters' => $mostActiveVoters
        ];
    }
} 