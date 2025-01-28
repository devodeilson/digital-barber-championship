<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class BackupMedia extends Command
{
    protected $signature = 'backup:media';
    protected $description = 'Create a backup of media files';

    public function handle()
    {
        $this->info('Starting media backup...');

        $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
        $filename = "media-backup-{$timestamp}.zip";
        $disk = Storage::disk('s3');

        try {
            $zip = new ZipArchive();
            $zip->open(storage_path("app/backup/{$filename}"), ZipArchive::CREATE | ZipArchive::OVERWRITE);

            // Adicionar arquivos de mídia ao ZIP
            $this->addDirectoryToZip($zip, storage_path('app/public/images'));
            $this->addDirectoryToZip($zip, storage_path('app/public/videos'));
            
            $zip->close();

            // Enviar para S3
            $disk->putFileAs(
                'backups/media',
                storage_path("app/backup/{$filename}"),
                $filename
            );

            // Limpar arquivos antigos
            $this->cleanOldBackups($disk);

            $this->info('Media backup completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }

    protected function addDirectoryToZip($zip, $path)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($path) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    protected function cleanOldBackups($disk)
    {
        $files = $disk->files('backups/media');
        $cutoff = Carbon::now()->subDays(7);

        foreach ($files as $file) {
            $timestamp = Carbon::createFromFormat(
                'Y-m-d-H-i-s',
                substr($file, 12, 19)
            );

            if ($timestamp->lt($cutoff)) {
                $disk->delete($file);
            }
        }
    }
} 