<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Championship;
use App\Models\ChampionshipCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChampionshipController extends Controller
{
    public function index()
    {
        $championships = Championship::with('categories')
            ->withCount('participants')
            ->latest()
            ->paginate(10);

        return view('admin.championships.index', compact('championships'));
    }

    public function create()
    {
        return view('admin.championships.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'entry_fee' => 'required|numeric|min:0',
            'pix_key' => 'required|string',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start',
            'voting_start' => 'required|date|after:registration_end',
            'voting_end' => 'required|date|after:voting_start',
            'image' => 'nullable|image|max:2048',
            'categories' => 'required|array|min:1',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.description' => 'nullable|string'
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('championships', 'public');
        }

        $championship = Championship::create($validated);

        foreach ($validated['categories'] as $category) {
            $championship->categories()->create($category);
        }

        return redirect()
            ->route('admin.championships.index')
            ->with('success', 'Campeonato criado com sucesso!');
    }

    public function edit(Championship $championship)
    {
        $championship->load('categories');
        return view('admin.championships.edit', compact('championship'));
    }

    public function update(Request $request, Championship $championship)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'entry_fee' => 'required|numeric|min:0',
            'pix_key' => 'required|string',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start',
            'voting_start' => 'required|date|after:registration_end',
            'voting_end' => 'required|date|after:voting_start',
            'image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,active,voting,finished',
            'categories' => 'required|array|min:1',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.description' => 'nullable|string'
        ]);

        if ($request->hasFile('image')) {
            if ($championship->image) {
                Storage::disk('public')->delete($championship->image);
            }
            $validated['image'] = $request->file('image')->store('championships', 'public');
        }

        $championship->update($validated);

        // Atualiza categorias
        $championship->categories()->delete();
        foreach ($validated['categories'] as $category) {
            $championship->categories()->create($category);
        }

        return redirect()
            ->route('admin.championships.index')
            ->with('success', 'Campeonato atualizado com sucesso!');
    }

    public function destroy(Championship $championship)
    {
        if ($championship->image) {
            Storage::disk('public')->delete($championship->image);
        }

        $championship->delete();

        return redirect()
            ->route('admin.championships.index')
            ->with('success', 'Campeonato excluído com sucesso!');
    }

    public function show(Championship $championship)
    {
        $championship->load(['categories', 'participants.user', 'videos.user']);
        return view('admin.championships.show', compact('championship'));
    }
}
