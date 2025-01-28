<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Mostra o dashboard do usuário
     */
    public function index()
    {
        $user = Auth::user();

        // Busca os campeonatos do usuário
        $championships = Championship::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Busca as submissões do usuário
        $submissions = Submission::where('user_id', $user->id)
            ->with('championship')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Estatísticas gerais
        $stats = [
            'total_championships' => Championship::where('user_id', $user->id)->count(),
            'total_submissions' => Submission::where('user_id', $user->id)->count(),
            'total_votes_received' => Submission::where('user_id', $user->id)->sum('total_votes'),
            'average_rating' => Submission::where('user_id', $user->id)
                ->where('total_votes', '>', 0)
                ->avg('average_rating') ?? 0
        ];

        return view('dashboard', compact('championships', 'submissions', 'stats'));
    }
}
