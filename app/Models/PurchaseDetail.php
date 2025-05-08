<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'item_id',
        'quantity',
        'unit_price',
        'subtotal',
        'received_quantity',
        'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'received_quantity' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Using saving event to ensure subtotal is set before database insert/update
        static::saving(function ($model) {
            // Calculate subtotal if it's not already set
            if (empty($model->subtotal) && isset($model->quantity) && isset($model->unit_price)) {
                $model->subtotal = $model->quantity * $model->unit_price;
            }
        });
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}