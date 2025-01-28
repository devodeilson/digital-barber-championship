<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SystemTextController extends Controller
{
    public function index()
    {
        $groups = SystemText::select('group')
            ->distinct()
            ->pluck('group');

        $texts = SystemText::orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('admin.system-texts.index', compact('groups', 'texts'));
    }

    public function batchUpdate(Request $request)
    {
        try {
            $texts = $request->input('texts', []);

            foreach ($texts as $id => $data) {
                SystemText::where('id', $id)->update([
                    'content_pt' => $data['content_pt'] ?? null,
                    'content_en' => $data['content_en'] ?? null,
                    'content_es' => $data['content_es'] ?? null,
                ]);
            }

            // Limpa o cache
            Cache::tags('system_texts')->flush();

            return redirect()
                ->route('admin.system-texts.index')
                ->with('success', 'Textos atualizados com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.system-texts.index')
                ->with('error', 'Erro ao atualizar textos: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view('admin.system-texts.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'key' => 'required|string|unique:system_texts,key',
                'group' => 'required|string',
                'description' => 'required|string',
                'content_pt' => 'nullable|string',
                'content_en' => 'nullable|string',
                'content_es' => 'nullable|string',
            ]);

            SystemText::create($validated);

            Cache::tags(['system_texts'])->flush();

            return redirect()
                ->route('admin.system-texts.index')
                ->with('success', 'Texto criado com sucesso!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar texto: ' . $e->getMessage());
        }
    }
}
