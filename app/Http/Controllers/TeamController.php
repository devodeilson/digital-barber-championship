<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::withCount('members')
            ->orderBy('total_points', 'desc')
            ->paginate(12);

        return view('teams.index', compact('teams'));
    }

    public function show(Team $team)
    {
        $team->load(['leader', 'members']);
        return view('teams.show', compact('team'));
    }

    public function create()
    {
        return view('teams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams',
            'country' => 'required|string|max:100',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('team-logos', 'public');
        }

        $validated['leader_id'] = auth()->id();

        $team = Team::create($validated);
        $team->addMember(auth()->user(), 'leader');

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Equipe criada com sucesso!');
    }

    public function edit(Team $team)
    {
        $this->authorize('update', $team);
        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,'.$team->id,
            'country' => 'required|string|max:100',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            if ($team->logo) {
                Storage::delete($team->logo);
            }
            $validated['logo'] = $request->file('logo')->store('team-logos', 'public');
        }

        $team->update($validated);

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Equipe atualizada com sucesso!');
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        if ($team->logo) {
            Storage::delete($team->logo);
        }

        $team->delete();

        return redirect()
            ->route('teams.index')
            ->with('success', 'Equipe excluída com sucesso!');
    }

    public function addMember(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($validated['user_id']);
        $team->addMember($user);

        return back()->with('success', 'Membro adicionado com sucesso!');
    }

    public function removeMember(Team $team, User $user)
    {
        $this->authorize('update', $team);

        if ($user->id === $team->leader_id) {
            return back()->with('error', 'Não é possível remover o líder da equipe.');
        }

        $team->removeMember($user);

        return back()->with('success', 'Membro removido com sucesso!');
    }
}
