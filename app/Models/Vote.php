<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'user_id',
        'rating'
    ];

    protected $casts = [
        'rating' => 'float'
    ];

    // Relacionamentos
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        // Após criar ou atualizar, recalcula a média do conteúdo
        static::saved(function ($vote) {
            $vote->video->updateRating();
        });

        // Após deletar, recalcula a média do conteúdo
        static::deleted(function ($vote) {
            $vote->video->updateRating();
        });
    }

    // Validações
    public static function rules()
    {
        return [
            'rating' => ['required', 'numeric', 'min:1', 'max:5']
        ];
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByVideo($query, $videoId)
    {
        return $query->where('video_id', $videoId);
    }

    public function scopeWithRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    // Métodos
    public function canBeEditedBy(User $user)
    {
        return $this->user_id === $user->id &&
               $this->created_at->addHours(24)->isFuture();
    }
}
