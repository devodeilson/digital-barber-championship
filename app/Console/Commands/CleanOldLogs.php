<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanOldLogs extends Command
{
    protected $signature = 'logs:clean {--days=30}';
    protected $description = 'Clean old log files';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        $count = 0;

        $files = Storage::disk('logs')->files();
        
        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(
                Storage::disk('logs')->lastModified($file)
            );

            if ($lastModified->lt($cutoffDate)) {
                Storage::disk('logs')->delete($file);
                $count++;
            }
        }

        $this->info("Cleaned {$count} old log files.");
    }
} 