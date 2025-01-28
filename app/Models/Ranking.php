<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    protected $fillable = [
        'championship_id',
        'user_id',
        'position',
        'score',
        'type',
        'ranking_date'
    ];

    protected $casts = [
        'ranking_date' => 'date',
        'score' => 'decimal:2'
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    public function scopeGeneral($query)
    {
        return $query->where('type', 'general');
    }

    public function scopeFinal($query)
    {
        return $query->where('type', 'final');
    }
}
