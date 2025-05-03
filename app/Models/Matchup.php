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
        'winner_item_id',
        'status',
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

    public function winner(): BelongsTo
    {
        return $this->belongsTo(DecisionListItem::class, 'winner_item_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
