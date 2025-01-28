<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Services\StorageService;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'phone',
        'country',
        'avatar',
        'avatar_disk',
        'role',
        'profile_photo',
        'points',
        'rating',
        'last_login_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'last_login_at' => 'datetime',
        'rating' => 'decimal:2'
    ];

    public function championships()
    {
        return $this->belongsToMany(Championship::class, 'championship_participants')
            ->withPivot('status', 'payment_id', 'payment_date')
            ->withTimestamps();
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function rankings()
    {
        return $this->hasMany(Ranking::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeCompetitors($query)
    {
        return $query->where('role', 'competitor');
    }

    public function scopeVoters($query)
    {
        return $query->where('role', 'voter');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function isAdmin()
    {
        return $this->is_admin === true;
    }

    public function isCompetitor()
    {
        return $this->role === 'competitor';
    }

    public function canVote()
    {
        return in_array($this->role, ['voter', 'competitor', 'admin']);
    }

    public function updateRating()
    {
        $averageRating = $this->contents()
            ->where('status', 'approved')
            ->avg('average_rating');

        $this->update(['rating' => $averageRating ?? 0]);
    }

    public function addPoints($points)
    {
        $this->increment('points', $points);
    }

    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return asset('images/default-avatar.png');
        }

        return app(StorageService::class)->url($this->avatar, $this->avatar_disk);
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo
            ? Storage::url($this->profile_photo)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    public function isParticipatingIn(Championship $championship): bool
    {
        return $this->championships()
            ->wherePivot('championship_id', $championship->id)
            ->wherePivotIn('status', ['paid', 'confirmed'])
            ->exists();
    }
}
