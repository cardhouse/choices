<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class List extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'claimed_at',
        'is_anonymous',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'is_anonymous' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function matchups(): HasMany
    {
        return $this->hasMany(Matchup::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shareCodes(): HasMany
    {
        return $this->hasMany(ShareCode::class);
    }
} 