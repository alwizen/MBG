<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemReturnDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'item_id',
        'quantity',
        'reason_detail',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function return(): BelongsTo
    {
        return $this->belongsTo(ItemReturn::class, 'return_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}