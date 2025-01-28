<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChampionshipCategory extends Model
{
    protected $fillable = [
        'championship_id',
        'name',
        'description'
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'category_id');
    }
}
