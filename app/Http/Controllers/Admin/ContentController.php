<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Championship;
use Illuminate\Http\Request;
use App\Http\Requests\ContentRequest;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ContentApproved;
use App\Notifications\ContentRejected;

class ContentController extends Controller
{
    public function index()
    {
        $contents = Content::with(['user', 'championship'])
            ->latest()
            ->paginate(10);

        return view('admin.contents.index', compact('contents'));
    }

    public function show(Content $content)
    {
        $content->load(['user', 'championship']);
        return view('admin.contents.show', compact('content'));
    }

    public function edit(Content $content)
    {
        $championships = Championship::where('status', 'active')->get();
        return view('admin.contents.edit', compact('content', 'championships'));
    }

    public function update(Request $request, Content $content)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'championship_id' => 'required|exists:championships,id',
            'status' => 'required|in:pending,approved,rejected',
            'feedback' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($content->image) {
                Storage::disk('public')->delete($content->image);
            }
            $validated['image'] = $request->file('image')->store('contents', 'public');
        }

        $content->update($validated);

        return redirect()->route('admin.contents.index')
            ->with('success', 'Conteúdo atualizado com sucesso!');
    }

    public function destroy(Content $content)
    {
        if ($content->image) {
            Storage::disk('public')->delete($content->image);
        }

        $content->delete();

        return redirect()->route('admin.contents.index')
            ->with('success', 'Conteúdo excluído com sucesso!');
    }

    public function approve(Content $content)
    {
        $content->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Conteúdo aprovado com sucesso!');
    }

    public function reject(Request $request, Content $content)
    {
        $validated = $request->validate([
            'feedback' => 'required|string'
        ]);

        $content->update([
            'status' => 'rejected',
            'feedback' => $validated['feedback']
        ]);

        return redirect()->back()->with('success', 'Conteúdo rejeitado com sucesso!');
    }
}
