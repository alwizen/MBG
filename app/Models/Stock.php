<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'quantity',
        'batch_number',
        'expiry_date',
        'location',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}