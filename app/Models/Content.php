<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'video_path',
        'championship_id',
        'user_id',
        'average_rating',
        'total_votes'
    ];

    protected $casts = [
        'average_rating' => 'decimal:2'
    ];

    protected $attributes = [
        'status' => 'pending'
    ];

    // Relacionamentos
    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Métodos
    public function approve()
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        // Notificar usuário
        $this->user->notify(new ContentApproved($this));
    }

    public function reject()
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now()
        ]);

        // Notificar usuário
        $this->user->notify(new ContentRejected($this));
    }

    public function updateRating()
    {
        $stats = $this->votes()
            ->selectRaw('AVG(rating) as average_rating, COUNT(*) as total_votes')
            ->first();

        $this->update([
            'average_rating' => $stats->average_rating ?? 0,
            'total_votes' => $stats->total_votes ?? 0
        ]);
    }

    public function getMediaUrlAttribute()
    {
        return $this->media ? Storage::url($this->media) : null;
    }

    public function isImage()
    {
        return $this->media && in_array(
            strtolower(pathinfo($this->media, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'gif']
        );
    }

    public function isVideo()
    {
        return $this->media && in_array(
            strtolower(pathinfo($this->media, PATHINFO_EXTENSION)),
            ['mp4', 'mov', 'avi']
        );
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        // Antes de deletar, remove a mídia
        static::deleting(function ($content) {
            if ($content->media) {
                Storage::delete($content->media);
            }
        });
    }
}
