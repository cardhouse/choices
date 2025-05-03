<?php

namespace App\Models;

use App\Services\ListClaimService;
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

    protected $attributes = [
        'is_anonymous' => false,
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

    /**
     * Schedule the list for deletion if it is anonymous.
     */
    public function scheduleDeletion(): void
    {
        if ($this->is_anonymous) {
            app(ListClaimService::class)->scheduleForDeletion($this);
        }
    }
}
