<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'matchup_id',
        'user_id',
        'chosen_item_id',
        'session_token',
        'ip_address',
        'user_agent',
    ];

    public function matchup(): BelongsTo
    {
        return $this->belongsTo(Matchup::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chosenItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'chosen_item_id');
    }
}
