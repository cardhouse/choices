<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'user_id',
        'share_code_id',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(DecisionList::class, 'list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shareCode(): BelongsTo
    {
        return $this->belongsTo(ShareCode::class);
    }
}
