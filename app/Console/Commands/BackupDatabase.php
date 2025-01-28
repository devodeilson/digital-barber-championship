<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Create a backup of the database';

    public function handle()
    {
        $this->info('Starting database backup...');

        $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
        $disk = Storage::disk('s3');

        try {
            // Criar backup do MySQL
            $command = sprintf(
                'mysqldump -u%s -p%s %s > %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                storage_path('app/backup/' . $filename)
            );

            exec($command);

            // Comprimir o arquivo
            $zipFilename = $filename . '.gz';
            $command = sprintf(
                'gzip %s',
                storage_path('app/backup/' . $filename)
            );

            exec($command);

            // Enviar para S3
            $disk->putFileAs(
                'backups/database',
                storage_path('app/backup/' . $zipFilename),
                $zipFilename
            );

            // Limpar arquivos antigos (manter últimos 7 dias)
            $this->cleanOldBackups($disk);

            $this->info('Database backup completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }

    protected function cleanOldBackups($disk)
    {
        $files = $disk->files('backups/database');
        $cutoff = Carbon::now()->subDays(7);

        foreach ($files as $file) {
            $timestamp = Carbon::createFromFormat(
                'Y-m-d-H-i-s',
                substr($file, 7, 19)
            );

            if ($timestamp->lt($cutoff)) {
                $disk->delete($file);
            }
        }
    }
} 