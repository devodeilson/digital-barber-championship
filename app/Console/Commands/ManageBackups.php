<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class ManageBackups extends Command
{
    protected $signature = 'backup:manage
                          {action : Action to perform (create/list/restore/delete)}
                          {--disk=local : Storage disk to use}
                          {--file= : Specific backup file to restore/delete}';

    protected $description = 'Manage system backups';

    public function handle()
    {
        $action = $this->argument('action');
        $disk = $this->option('disk');

        switch ($action) {
            case 'create':
                $this->createBackup($disk);
                break;
            case 'list':
                $this->listBackups($disk);
                break;
            case 'restore':
                $this->restoreBackup($disk);
                break;
            case 'delete':
                $this->deleteBackup($disk);
                break;
            default:
                $this->error('Invalid action specified');
        }
    }

    protected function createBackup($disk)
    {
        $this->info('Creating backup...');

        try {
            // Criar arquivo ZIP
            $filename = 'backup-' . now()->format('Y-m-d-H-i-s') . '.zip';
            $zip = new ZipArchive();
            $tempPath = storage_path('app/temp/' . $filename);

            if ($zip->open($tempPath, ZipArchive::CREATE) === TRUE) {
                // Adicionar arquivos do sistema
                $this->addFilesToZip($zip, base_path(), '');
                
                // Adicionar dump do banco de dados
                $this->addDatabaseDump($zip);

                $zip->close();

                // Mover para o disco de destino
                Storage::disk($disk)->put($filename, file_get_contents($tempPath));
                unlink($tempPath);

                $this->info('Backup created successfully: ' . $filename);
            }
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
        }
    }

    protected function listBackups($disk)
    {
        $files = Storage::disk($disk)->files('backup-*');
        
        $headers = ['Filename', 'Size', 'Last Modified'];
        $rows = [];

        foreach ($files as $file) {
            $rows[] = [
                $file,
                $this->formatBytes(Storage::disk($disk)->size($file)),
                Carbon::createFromTimestamp(Storage::disk($disk)->lastModified($file))->format('Y-m-d H:i:s')
            ];
        }

        $this->table($headers, $rows);
    }

    protected function restoreBackup($disk)
    {
        $file = $this->option('file');
        if (!$file) {
            $file = $this->choice(
                'Which backup would you like to restore?',
                Storage::disk($disk)->files('backup-*')
            );
        }

        if (!Storage::disk($disk)->exists($file)) {
            $this->error('Backup file not found');
            return;
        }

        if (!$this->confirm('Are you sure you want to restore this backup? This will overwrite current data!')) {
            return;
        }

        $this->info('Restoring backup...');

        try {
            $tempPath = storage_path('app/temp/restore.zip');
            Storage::disk($disk)->copy($file, $tempPath);

            $zip = new ZipArchive();
            if ($zip->open($tempPath) === TRUE) {
                $zip->extractTo(base_path());
                $zip->close();

                // Restaurar banco de dados
                $this->restoreDatabaseFromBackup($tempPath);

                unlink($tempPath);
                $this->info('Backup restored successfully');
            }
        } catch (\Exception $e) {
            $this->error('Restore failed: ' . $e->getMessage());
        }
    }

    protected function deleteBackup($disk)
    {
        $file = $this->option('file');
        if (!$file) {
            $file = $this->choice(
                'Which backup would you like to delete?',
                Storage::disk($disk)->files('backup-*')
            );
        }

        if (!Storage::disk($disk)->exists($file)) {
            $this->error('Backup file not found');
            return;
        }

        if ($this->confirm('Are you sure you want to delete this backup?')) {
            Storage::disk($disk)->delete($file);
            $this->info('Backup deleted successfully');
        }
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }

    // Métodos auxiliares para manipulação de arquivos e banco de dados
    protected function addFilesToZip($zip, $path, $relativePath)
    {
        $excludeDirs = ['vendor', 'node_modules', 'storage/logs'];
        $excludeFiles = ['.git', '.env'];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen(base_path()) + 1);

            // Verificar exclusões
            $exclude = false;
            foreach ($excludeDirs as $dir) {
                if (strpos($relativePath, $dir) === 0) {
                    $exclude = true;
                    break;
                }
            }

            if (!$exclude) {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    protected function addDatabaseDump($zip)
    {
        $dumpFile = storage_path('app/temp/database.sql');
        
        // Executar dump do banco
        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            $dumpFile
        );
        
        exec($command);

        if (file_exists($dumpFile)) {
            $zip->addFile($dumpFile, 'database.sql');
            unlink($dumpFile);
        }
    }

    protected function restoreDatabaseFromBackup($zipPath)
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo(storage_path('app/temp'), 'database.sql');
            $zip->close();

            $dumpFile = storage_path('app/temp/database.sql');
            
            if (file_exists($dumpFile)) {
                $command = sprintf(
                    'mysql -u%s -p%s %s < %s',
                    config('database.connections.mysql.username'),
                    config('database.connections.mysql.password'),
                    config('database.connections.mysql.database'),
                    $dumpFile
                );
                
                exec($command);
                unlink($dumpFile);
            }
        }
    }
} 