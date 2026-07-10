<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matchup extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'item_a_id',
        'item_b_id',
        'round_number',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(DecisionList::class, 'list_id');
    }

    public function itemA(): BelongsTo
    {
        return $this->belongsTo(DecisionListItem::class, 'item_a_id');
    }

    public function itemB(): BelongsTo
    {
        return $this->belongsTo(DecisionListItem::class, 'item_b_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Whether the given item is one of the two items in this matchup.
     */
    public function involves(int $itemId): bool
    {
        return $itemId === $this->item_a_id || $itemId === $this->item_b_id;
    }
}
