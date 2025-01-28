<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CacheService
{
    protected $defaultTtl = 3600; // 1 hora em segundos

    public function remember($key, $callback, $ttl = null)
    {
        return Cache::remember($key, $ttl ?? $this->defaultTtl, $callback);
    }

    public function forget($key)
    {
        return Cache::forget($key);
    }

    public function flush()
    {
        return Cache::flush();
    }

    public function tags($tags)
    {
        return Cache::tags($tags);
    }

    // Chaves específicas para cada entidade
    public function getChampionshipKey($id = null)
    {
        return $id ? "championship:{$id}" : "championships:all";
    }

    public function getContentKey($id = null, $championshipId = null)
    {
        if ($id) {
            return "content:{$id}";
        }
        if ($championshipId) {
            return "championship:{$championshipId}:contents";
        }
        return "contents:all";
    }

    public function getVoteKey($contentId = null, $userId = null)
    {
        if ($contentId && $userId) {
            return "content:{$contentId}:user:{$userId}:vote";
        }
        if ($contentId) {
            return "content:{$contentId}:votes";
        }
        return "votes:all";
    }

    public function getUserKey($id = null)
    {
        return $id ? "user:{$id}" : "users:all";
    }

    // Métodos para invalidar cache
    public function invalidateChampionship($id)
    {
        $keys = [
            $this->getChampionshipKey($id),
            $this->getChampionshipKey()
        ];

        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    public function invalidateContent($id, $championshipId = null)
    {
        $keys = [
            $this->getContentKey($id),
            $this->getContentKey(null, $championshipId),
            $this->getContentKey()
        ];

        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    public function invalidateVote($contentId, $userId = null)
    {
        $keys = [
            $this->getVoteKey($contentId, $userId),
            $this->getVoteKey($contentId),
            $this->getVoteKey()
        ];

        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    public function invalidateUser($id)
    {
        $keys = [
            $this->getUserKey($id),
            $this->getUserKey()
        ];

        foreach ($keys as $key) {
            $this->forget($key);
        }
    }
} 