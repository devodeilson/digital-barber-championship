<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;
use Exception;

class StorageService
{
    private $primaryDisk;
    private $fallbackDisk;

    public function __construct()
    {
        $this->primaryDisk = config('filesystems.default', 's3');
        $this->fallbackDisk = 'public';
    }

    public function store($file, $path = '', $fileName = null)
    {
        try {
            if (!$fileName) {
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            }

            $fullPath = trim($path . '/' . $fileName, '/');

            // Tenta armazenar no disco primário
            if ($this->primaryDisk !== 'public') {
                try {
                    return [
                        'path' => Storage::disk($this->primaryDisk)->putFileAs($path, $file, $fileName),
                        'disk' => $this->primaryDisk
                    ];
                } catch (Exception $e) {
                    \Log::warning("Falha ao usar armazenamento primário: " . $e->getMessage());
                }
            }

            // Se falhar ou for configurado para usar local, usa o disco local
            return [
                'path' => Storage::disk($this->fallbackDisk)->putFileAs($path, $file, $fileName),
                'disk' => $this->fallbackDisk
            ];
        } catch (Exception $e) {
            throw new Exception("Erro ao armazenar arquivo: " . $e->getMessage());
        }
    }

    public function delete($path, $disk = null)
    {
        if (!$path) return true;

        try {
            if ($disk) {
                return Storage::disk($disk)->delete($path);
            }

            // Tenta deletar de ambos os discos para garantir
            $deleted = Storage::disk($this->primaryDisk)->delete($path);
            Storage::disk($this->fallbackDisk)->delete($path);

            return $deleted;
        } catch (Exception $e) {
            \Log::error("Erro ao deletar arquivo: " . $e->getMessage());
            return false;
        }
    }

    public function url($path, $disk = null)
    {
        if (!$path) return null;

        try {
            if ($disk) {
                return Storage::disk($disk)->url($path);
            }

            // Tenta obter a URL do disco primário primeiro
            try {
                return Storage::disk($this->primaryDisk)->url($path);
            } catch (Exception $e) {
                return Storage::disk($this->fallbackDisk)->url($path);
            }
        } catch (Exception $e) {
            \Log::error("Erro ao gerar URL do arquivo: " . $e->getMessage());
            return null;
        }
    }

    public function uploadImage($file, $path = 'images', $resize = true)
    {
        try {
            if ($resize) {
                $image = Image::make($file);
                $image->resize(1200, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $fullPath = $path . '/' . $filename;

                $this->disk->put($fullPath, $image->stream()->__toString(), 'public');

                return $fullPath;
            }

            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $fullPath = $path . '/' . $filename;

            $this->disk->putFileAs($path, $file, $filename, 'public');

            return $fullPath;
        } catch (Exception $e) {
            \Log::error('Failed to upload image', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);

            throw $e;
        }
    }

    public function uploadVideo($file, $path = 'videos')
    {
        try {
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $fullPath = $path . '/' . $filename;

            $this->disk->putFileAs($path, $file, $filename, 'public');

            return $fullPath;
        } catch (Exception $e) {
            \Log::error('Failed to upload video', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);

            throw $e;
        }
    }

    public function getUrl($path)
    {
        try {
            return $this->disk->url($path);
        } catch (Exception $e) {
            \Log::error('Failed to get file URL', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);

            throw $e;
        }
    }
}
