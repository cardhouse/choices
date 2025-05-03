<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(DecisionList::class, 'list_id');
    }
} 