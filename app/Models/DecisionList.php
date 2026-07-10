<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
        'voting_closes_at',
        'voting_closed_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'voting_closes_at' => 'datetime',
        'voting_closed_at' => 'datetime',
    ];

    protected $attributes = [
        'is_anonymous' => false,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(DecisionListItem::class, 'list_id');
    }

    public function matchups(): HasMany
    {
        return $this->hasMany(Matchup::class, 'list_id');
    }

    public function votes(): HasManyThrough
    {
        return $this->hasManyThrough(Vote::class, Matchup::class, 'list_id', 'matchup_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shareCodes(): HasMany
    {
        return $this->hasMany(ShareCode::class, 'list_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ListParticipant::class, 'list_id');
    }

    public function participantUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'list_participants', 'list_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * The currently active share code, if any.
     */
    public function activeShareCode(): ?ShareCode
    {
        return $this->shareCodes()->active()->latest()->first();
    }

    /**
     * Whether the list currently has an active share code.
     */
    public function isShared(): bool
    {
        return $this->shareCodes()->active()->exists();
    }

    /**
     * Whether voting is closed, either manually or because the deadline passed.
     */
    public function isVotingClosed(): bool
    {
        if ($this->voting_closed_at !== null) {
            return true;
        }

        return $this->voting_closes_at !== null && $this->voting_closes_at->isPast();
    }

    /**
     * Whether voting is still open.
     */
    public function isVotingOpen(): bool
    {
        return ! $this->isVotingClosed();
    }

    /**
     * Whether the given user is the owner of the list.
     */
    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }

    /**
     * Whether the given user joined this list via a share code.
     */
    public function hasParticipant(?User $user): bool
    {
        return $user !== null && $this->participants()->where('user_id', $user->id)->exists();
    }
}
