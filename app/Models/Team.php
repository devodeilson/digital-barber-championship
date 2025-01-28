<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Team extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'country',
        'description',
        'logo',
        'leader_id',
        'total_points',
        'ranking_position'
    ];

    // Relacionamentos
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    // Métodos
    public function addMember(User $user, $role = 'member')
    {
        if (!$this->members()->where('user_id', $user->id)->exists()) {
            $this->members()->attach($user->id, ['role' => $role]);
        }
    }

    public function removeMember(User $user)
    {
        $this->members()->detach($user->id);
    }

    public function updatePoints()
    {
        $this->total_points = $this->members()
            ->sum('points');
        $this->save();
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo
            ? Storage::url($this->logo)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }
}
