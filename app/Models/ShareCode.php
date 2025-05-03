<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareCode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'list_id',
        'code',
        'expires_at',
        'deactivated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    /**
     * Get the list that owns the share code.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(DecisionList::class, 'list_id');
    }

    /**
     * Scope a query to only include active codes.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deactivated_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
