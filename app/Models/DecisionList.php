<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DecisionList extends Model
{
    use HasFactory;

    protected $table = 'decision_lists';

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
        return $this->hasMany(Item::class, 'list_id');
    }

    public function matchups(): HasMany
    {
        return $this->hasMany(Matchup::class, 'list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shareCodes(): HasMany
    {
        return $this->hasMany(ShareCode::class, 'list_id');
    }
}
