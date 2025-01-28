<?php

namespace App\Traits;

use App\Services\CacheService;

trait Cacheable
{
    protected static function bootCacheable()
    {
        static::created(function ($model) {
            $model->invalidateListCache();
        });

        static::updated(function ($model) {
            $model->invalidateModelCache();
            $model->invalidateListCache();
        });

        static::deleted(function ($model) {
            $model->invalidateModelCache();
            $model->invalidateListCache();
        });
    }

    public function getCacheService()
    {
        return app(CacheService::class);
    }

    public function getCacheKey()
    {
        return strtolower(class_basename($this)) . ":{$this->id}";
    }

    public function getListCacheKey()
    {
        return strtolower(class_basename($this)) . ':all';
    }

    public function invalidateModelCache()
    {
        return $this->getCacheService()->forget($this->getCacheKey());
    }

    public function invalidateListCache()
    {
        return $this->getCacheService()->forget($this->getListCacheKey());
    }

    public static function cacheAll($ttl = 3600)
    {
        return static::cache(function () {
            return static::all();
        }, 'all', $ttl);
    }

    public static function cachePaginate($perPage = 15, $ttl = 3600)
    {
        $page = request()->get('page', 1);
        $key = static::class . ":page:{$page}:perPage:{$perPage}";

        return static::cache(function () use ($perPage) {
            return static::paginate($perPage);
        }, $key, $ttl);
    }

    public static function cacheFind($id, $ttl = 3600)
    {
        return static::cache(function () use ($id) {
            return static::find($id);
        }, $id, $ttl);
    }

    protected static function cache($callback, $key, $ttl)
    {
        $cacheService = app(CacheService::class);
        $modelKey = strtolower(class_basename(static::class));
        $fullKey = "{$modelKey}:{$key}";

        return $cacheService->remember($fullKey, $callback, $ttl);
    }
} 