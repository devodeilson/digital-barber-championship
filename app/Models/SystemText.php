<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemText extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'group',
        'content_pt',
        'content_en',
        'content_es',
        'description'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
