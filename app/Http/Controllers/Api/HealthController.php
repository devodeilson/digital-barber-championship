<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class HealthController extends ApiController
{
    public function check()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'memory' => $this->checkMemory(),
            'cpu' => $this->checkCPU()
        ];

        $status = !in_array(false, array_column($checks, 'status'));

        return response()->json([
            'status' => $status ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks
        ], $status ? 200 : 503);
    }

    protected function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            $result = [
                'status' => true,
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $result = [
                'status' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }

        return $result;
    }

    protected function checkRedis()
    {
        try {
            Redis::ping();
            $result = [
                'status' => true,
                'message' => 'Redis connection successful'
            ];
        } catch (\Exception $e) {
            $result = [
                'status' => false,
                'message' => 'Redis connection failed: ' . $e->getMessage()
            ];
        }

        return $result;
    }

    protected function checkStorage()
    {
        try {
            Storage::disk('s3')->put('health_check.txt', 'ok');
            Storage::disk('s3')->delete('health_check.txt');
            $result = [
                'status' => true,
                'message' => 'Storage service is working'
            ];
        } catch (\Exception $e) {
            $result = [
                'status' => false,
                'message' => 'Storage service failed: ' . $e->getMessage()
            ];
        }

        return $result;
    }

    protected function checkCache()
    {
        try {
            Cache::put('health_check', 'ok', 10);
            Cache::get('health_check');
            Cache::forget('health_check');
            $result = [
                'status' => true,
                'message' => 'Cache service is working'
            ];
        } catch (\Exception $e) {
            $result = [
                'status' => false,
                'message' => 'Cache service failed: ' . $e->getMessage()
            ];
        }

        return $result;
    }

    protected function checkQueue()
    {
        try {
            $connection = config('queue.default');
            $queue = config("queue.connections.{$connection}.queue");
            
            // Verificar se o worker está rodando
            $processCount = shell_exec("ps aux | grep 'queue:work' | grep -v grep | wc -l");
            
            $result = [
                'status' => (int)$processCount > 0,
                'message' => (int)$processCount > 0 ? 
                    'Queue worker is running' : 
                    'Queue worker is not running'
            ];
        } catch (\Exception $e) {
            $result = [
                'status' => false,
                'message' => 'Queue check failed: ' . $e->getMessage()
            ];
        }

        return $result;
    }

    protected function checkMemory()
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        return [
            'status' => true,
            'message' => 'Memory usage is normal',
            'details' => [
                'limit' => $memoryLimit,
                'usage' => $this->formatBytes($memoryUsage),
                'peak' => $this->formatBytes($memoryPeak)
            ]
        ];
    }

    protected function checkCPU()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $status = $load[0] < 0.8;

            return [
                'status' => $status,
                'message' => $status ? 'CPU load is normal' : 'CPU load is high',
                'details' => [
                    'load_1m' => $load[0],
                    'load_5m' => $load[1],
                    'load_15m' => $load[2]
                ]
            ];
        }

        return [
            'status' => true,
            'message' => 'CPU check not available'
        ];
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }
} 