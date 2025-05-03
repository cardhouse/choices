<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'label',
        'description',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(DecisionList::class, 'list_id');
    }

    public function matchupsAsItemA(): HasMany
    {
        return $this->hasMany(Matchup::class, 'item_a_id');
    }

    public function matchupsAsItemB(): HasMany
    {
        return $this->hasMany(Matchup::class, 'item_b_id');
    }
} 