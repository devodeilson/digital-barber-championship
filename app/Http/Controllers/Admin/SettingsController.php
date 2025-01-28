<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StorageService;

class SettingsController extends Controller
{
    protected $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function index()
    {
        $settings = [
            'site_name' => config('app.name'),
            'site_description' => config('app.description', ''),
            'contact_email' => config('mail.from.address', 'hello@example.com'),
            'social_media' => [
                'facebook' => config('social.facebook', ''),
                'instagram' => config('social.instagram', ''),
                'twitter' => config('social.twitter', '')
            ]
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'site_name' => 'required|string|max:255',
                'site_description' => 'required|string',
                'contact_email' => 'required|email',
                'social_media.facebook' => 'nullable|url',
                'social_media.instagram' => 'nullable|url',
                'social_media.twitter' => 'nullable|url',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'favicon' => 'nullable|image|mimes:ico,png|max:1024'
            ]);

            // Atualiza as configurações
            $this->updateEnvironmentFile([
                'APP_NAME' => $validated['site_name'],
                'APP_DESCRIPTION' => $validated['site_description'],
                'MAIL_FROM_ADDRESS' => $validated['contact_email'],
                'SOCIAL_FACEBOOK' => $validated['social_media']['facebook'] ?? '',
                'SOCIAL_INSTAGRAM' => $validated['social_media']['instagram'] ?? '',
                'SOCIAL_TWITTER' => $validated['social_media']['twitter'] ?? ''
            ]);

            // Processa uploads
            if ($request->hasFile('logo')) {
                $result = $this->storageService->store($request->file('logo'), 'settings', 'logo.png');
            }

            if ($request->hasFile('favicon')) {
                $result = $this->storageService->store($request->file('favicon'), 'settings', 'favicon.ico');
            }

            return redirect()
                ->route('admin.settings.index')
                ->with('success', 'Configurações atualizadas com sucesso!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }
    }

    private function updateEnvironmentFile($data)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $content = file_get_contents($path);

            foreach ($data as $key => $value) {
                $value = str_replace('"', '\"', $value);

                if (strpos($content, "{$key}=") !== false) {
                    $content = preg_replace(
                        "/^{$key}=.*/m",
                        "{$key}=\"{$value}\"",
                        $content
                    );
                } else {
                    $content .= "\n{$key}=\"{$value}\"";
                }
            }

            file_put_contents($path, $content);
        }
    }
}
