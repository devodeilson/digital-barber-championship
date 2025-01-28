<?php

namespace App\Traits;

use App\Services\Logger;

trait Loggable
{
    protected static function bootLoggable()
    {
        static::created(function ($model) {
            app(Logger::class)->activity('created', $model);
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            
            if (!empty($changes)) {
                app(Logger::class)->activity('updated', $model, [
                    'changes' => $changes,
                    'original' => array_intersect_key($model->getOriginal(), $changes)
                ]);
            }
        });

        static::deleted(function ($model) {
            app(Logger::class)->activity('deleted', $model);
        });
    }

    public function logActivity($action, $details = [])
    {
        app(Logger::class)->activity($action, $this, $details);
    }
} 