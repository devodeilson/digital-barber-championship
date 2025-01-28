<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Championship;
use App\Models\Content;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users_count' => User::count(),
            'championships_count' => Championship::count(),
            'contents_count' => Content::count(),
            'revenue' => Transaction::where('status', 'completed')->sum('amount') ?? 0
        ];

        $latestUsers = User::latest()->take(5)->get();
        $latestChampionships = Championship::latest()->take(5)->get();
        $latestContents = Content::with(['user', 'championship'])->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'latestUsers', 'latestChampionships', 'latestContents'));
    }

    public function users()
    {
        $users = User::withCount(['contents', 'championships'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function championships()
    {
        $championships = Championship::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.championships.index', compact('championships'));
    }

    public function contents()
    {
        $contents = Content::with(['user', 'championship'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.contents.index', compact('contents'));
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function settings()
    {
        return view('admin.settings', [
            'settings' => [
                'site_name' => config('app.name'),
                'contact_email' => config('mail.from.address'),
                'max_file_size' => config('filesystems.max_upload_size'),
                // Adicione outras configurações conforme necessário
            ]
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'max_file_size' => 'required|integer|min:1',
        ]);

        // Atualizar configurações
        // Você precisará implementar a lógica para salvar as configurações

        return back()->with('success', 'Configurações atualizadas com sucesso!');
    }
}
