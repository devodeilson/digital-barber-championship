<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    protected $fillable = [
        'championship_id',
        'user_id',
        'category_id',
        'title',
        'description',
        'youtube_id',
        'original_filename',
        'duration_seconds',
        'status',
        'error_message'
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ChampionshipCategory::class, 'category_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function getAverageRating(): float
    {
        return $this->votes()->avg('rating') ?? 0.0;
    }

    public function getTotalVotes(): int
    {
        return $this->votes()->count();
    }
}
