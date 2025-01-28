<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'championship_id',
        'amount',
        'payment_method',
        'payment_id',
        'status',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    protected $attributes = [
        'status' => 'pending'
    ];

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function championship()
    {
        return $this->belongsTo(Championship::class);
    }

    public function status_history()
    {
        return $this->hasMany(TransactionStatusHistory::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByChampionship($query, $championshipId)
    {
        return $query->where('championship_id', $championshipId);
    }

    // Métodos
    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    public function markAsRefunded()
    {
        $this->update(['status' => 'refunded']);
    }

    public function addStatusHistory($status, $notes = null)
    {
        return $this->status_history()->create([
            'status' => $status,
            'notes' => $notes
        ]);
    }

    public function getFormattedAmountAttribute()
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
            'refunded' => 'blue'
        ][$this->status] ?? 'gray';
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        // Ao criar, gera um ID único para o pagamento
        static::creating(function ($transaction) {
            $transaction->transaction_id = 'PAY-' . strtoupper(uniqid());
        });
    }
}
