<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecisionListItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'decision_list_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'label',
        'list_id',
    ];

    /**
     * Get the list that owns the item.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(DecisionList::class, 'list_id');
    }
}
