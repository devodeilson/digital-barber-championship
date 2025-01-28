<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Championship;
use App\Models\Content;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Estatísticas gerais
        $stats = [
            'users_count' => User::count(),
            'championships_count' => Championship::count(),
            'contents_count' => Content::count(),
            'revenue' => Transaction::where('status', 'completed')->sum('amount') ?? 0,
            'active_championships' => Championship::where('status', 'active')->count(),
            'pending_contents' => Content::where('status', 'pending')->count()
        ];

        // Últimos usuários registrados
        $latestUsers = User::latest()
            ->take(5)
            ->get();

        // Últimos campeonatos
        $latestChampionships = Championship::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Últimos conteúdos
        $latestContents = Content::with(['user', 'championship'])
            ->latest()
            ->take(5)
            ->get();

        // Campeonatos ativos
        $activeChampionships = Championship::where('status', 'active')
            ->withCount('contents')
            ->orderBy('end_date', 'asc')
            ->take(5)
            ->get();

        // Melhores competidores
        $topCompetitors = User::withCount('contents')
            ->orderBy('contents_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'latestUsers',
            'latestChampionships',
            'latestContents',
            'activeChampionships',
            'topCompetitors'
        ));
    }
}
