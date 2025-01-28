<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Championship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function index()
    {
        $submissions = Submission::with(['championship', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('submissions.index', compact('submissions'));
    }

    public function create()
    {
        $championships = Championship::where('status', 'active')->get();
        return view('submissions.create', compact('championships'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'championship_id' => 'required|exists:championships,id',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        // Upload do arquivo
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('submissions');
            $validated['file_path'] = $path;
        }

        $submission = auth()->user()->submissions()->create($validated);

        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', 'Submissão enviada com sucesso!');
    }

    public function show(Submission $submission)
    {
        $submission->load(['championship', 'user', 'votes.user']);
        return view('submissions.show', compact('submission'));
    }

    public function edit(Submission $submission)
    {
        $this->authorize('update', $submission);
        $championships = Championship::where('status', 'active')->get();
        return view('submissions.edit', compact('submission', 'championships'));
    }

    public function update(Request $request, Submission $submission)
    {
        $this->authorize('update', $submission);

        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'file' => 'nullable|file|max:10240',
        ]);

        if ($request->hasFile('file')) {
            // Remove arquivo antigo
            if ($submission->file_path) {
                Storage::delete($submission->file_path);
            }

            // Upload novo arquivo
            $path = $request->file('file')->store('submissions');
            $validated['file_path'] = $path;
        }

        $submission->update($validated);

        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', 'Submissão atualizada com sucesso!');
    }

    public function destroy(Submission $submission)
    {
        $this->authorize('delete', $submission);

        // Remove arquivo
        if ($submission->file_path) {
            Storage::delete($submission->file_path);
        }

        $submission->delete();

        return redirect()
            ->route('submissions.index')
            ->with('success', 'Submissão excluída com sucesso!');
    }
}
