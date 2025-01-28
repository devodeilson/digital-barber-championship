<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Championship extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'entry_fee',
        'pix_key',
        'registration_start',
        'registration_end',
        'voting_start',
        'voting_end',
        'status',
        'image',
        'is_final_cup'
    ];

    protected $casts = [
        'registration_start' => 'datetime',
        'registration_end' => 'datetime',
        'voting_start' => 'datetime',
        'voting_end' => 'datetime',
        'entry_fee' => 'decimal:2',
        'is_final_cup' => 'boolean'
    ];

    protected $attributes = [
        'status' => 'draft'
    ];

    // Relacionamentos
    public function categories(): HasMany
    {
        return $this->hasMany(ChampionshipCategory::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'championship_participants')
            ->withPivot('status', 'payment_id', 'payment_date')
            ->withTimestamps();
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFinished($query)
    {
        return $query->where('status', 'finished');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('registration_start', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('registration_start', '<=', now())
                    ->where('registration_end', '>=', now());
    }

    // Métodos
    public function activate()
    {
        if ($this->status !== 'draft') {
            throw new \Exception('Apenas campeonatos em rascunho podem ser ativados.');
        }

        $this->update(['status' => 'active']);

        // Notificar participantes
        $this->participants->each->notify(new ChampionshipStarted($this));
    }

    public function finish()
    {
        if ($this->status !== 'active') {
            throw new \Exception('Apenas campeonatos ativos podem ser finalizados.');
        }

        $this->update(['status' => 'finished']);

        // Calcular vencedores
        $this->calculateWinners();

        // Notificar participantes
        $this->participants->each->notify(new ChampionshipFinished($this));
    }

    public function calculateWinners()
    {
        // Lógica para calcular os vencedores baseado nos votos dos conteúdos
        $winners = $this->contents()
            ->approved()
            ->orderByDesc('average_rating')
            ->take(3)
            ->get();

        // Registrar vencedores
        foreach ($winners as $index => $content) {
            $position = $index + 1;
            $content->user->notify(new ChampionshipWinner($this, $position));
        }

        return $winners;
    }

    public function canJoin(User $user)
    {
        return !$this->participants()->where('user_id', $user->id)->exists() &&
               $this->status === 'active' &&
               $this->participants()->count() < $this->max_participants &&
               $user->rating >= $this->min_rating;
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner ? Storage::url($this->banner) : null;
    }

    public function getFormattedEntryFeeAttribute()
    {
        return 'R$ ' . number_format($this->entry_fee, 2, ',', '.');
    }

    public function getStatusColorAttribute()
    {
        return [
            'draft' => 'gray',
            'active' => 'green',
            'finished' => 'blue'
        ][$this->status] ?? 'gray';
    }

    public function isRegistrationOpen(): bool
    {
        $now = now();
        return $now->between($this->registration_start, $this->registration_end);
    }

    public function isVotingOpen(): bool
    {
        $now = now();
        return $now->between($this->voting_start, $this->voting_end);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        // Antes de deletar, remove o banner
        static::deleting(function ($championship) {
            if ($championship->banner) {
                Storage::delete($championship->banner);
            }
        });
    }
}
