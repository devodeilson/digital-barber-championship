<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ChampionshipController extends Controller
{
    public function index()
    {
        $championships = Championship::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('championships.index', compact('championships'));
    }

    public function create()
    {
        return view('championships.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:barba,barbeiro,tosa,corte',
            'description' => 'required|string',
            'rules' => 'required|string',
            'entry_fee' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'registration_deadline' => 'required|date|before:start_date'
        ]);

        $championship = Championship::create([
            ...$validated,
            'user_id' => Auth::id(),
            'status' => 'draft'
        ]);

        return redirect()->route('championships.show', $championship)
            ->with('success', 'Campeonato criado com sucesso!');
    }

    public function show(Championship $championship)
    {
        $championship->load(['user', 'contents.user']);

        return view('championships.show', compact('championship'));
    }

    public function edit(Championship $championship)
    {
        $this->authorize('update', $championship);
        return view('championships.edit', compact('championship'));
    }

    public function update(Request $request, Championship $championship)
    {
        $this->authorize('update', $championship);

        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:draft,active,finished',
        ]);

        $championship->update($validated);

        return redirect()
            ->route('championships.show', $championship)
            ->with('success', 'Campeonato atualizado com sucesso!');
    }

    public function destroy(Championship $championship)
    {
        $this->authorize('delete', $championship);

        $championship->delete();

        return redirect()
            ->route('championships.index')
            ->with('success', 'Campeonato excluído com sucesso!');
    }

    public function registerParticipant(Request $request, Championship $championship)
    {
        $request->validate([
            'entry_code' => 'required|string|exists:transactions,entry_code'
        ]);

        $transaction = Transaction::where('entry_code', $request->entry_code)
            ->where('status', 'completed')
            ->where('user_id', auth()->id())
            ->first();

        if (!$transaction) {
            return back()->with('error', 'Código de entrada inválido.');
        }

        $championship->participants()->attach(auth()->id(), [
            'entry_status' => 'approved'
        ]);

        return redirect()->route('championships.show', $championship)
            ->with('success', 'Inscrição realizada com sucesso!');
    }

    public function adminIndex()
    {
        $this->authorize('viewAny', Championship::class);

        $championships = Championship::withCount('participants')
            ->orderBy('start_date', 'desc')
            ->paginate(15);

        return view('admin.championships.index', compact('championships'));
    }

    public function active(Request $request)
    {
        $query = Championship::where('status', 'active')
            ->withCount('contents');

        // Filtro por categoria
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Ordenação
        switch ($request->sort) {
            case 'prize':
                $query->orderBy('entry_fee', 'desc');
                break;
            case 'participants':
                $query->orderBy('contents_count', 'desc');
                break;
            default:
                $query->orderBy('start_date', 'desc');
                break;
        }

        $championships = $query->paginate(12);

        return view('championships.active', compact('championships'));
    }
}
