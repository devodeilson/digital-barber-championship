<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    protected $youtubeService;

    public function __construct(YouTubeService $youtubeService)
    {
        $this->youtubeService = $youtubeService;
    }

    public function create(Championship $championship)
    {
        if (!auth()->user()->isParticipatingIn($championship)) {
            return redirect()->back()->with('error', 'Você não está inscrito neste campeonato.');
        }

        return view('videos.create', compact('championship'));
    }

    public function store(Request $request, Championship $championship)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:championship_categories,id',
            'video' => 'required|file|mimetypes:video/mp4|max:100000'
        ]);

        $video = $request->file('video');
        $originalName = $video->getClientOriginalName();
        $path = $video->store('temp-videos');

        try {
            // Upload para o YouTube
            $youtubeId = $this->youtubeService->upload([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'file' => Storage::path($path)
            ]);

            // Criar registro do vídeo
            Video::create([
                'championship_id' => $championship->id,
                'user_id' => auth()->id(),
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'youtube_id' => $youtubeId,
                'original_filename' => $originalName,
                'status' => 'published'
            ]);

            Storage::delete($path);

            return redirect()
                ->route('championships.show', $championship)
                ->with('success', 'Vídeo enviado com sucesso!');

        } catch (\Exception $e) {
            Storage::delete($path);
            return redirect()
                ->back()
                ->with('error', 'Erro ao enviar vídeo: ' . $e->getMessage());
        }
    }
}
