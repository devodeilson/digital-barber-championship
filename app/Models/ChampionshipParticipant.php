<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChampionshipParticipant extends Model
{
    protected $fillable = [
        'championship_id',
        'user_id',
        'status',
        'payment_id',
        'payment_date'
    ];

    protected $casts = [
        'payment_date' => 'datetime'
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'confirmed']);
    }
}
