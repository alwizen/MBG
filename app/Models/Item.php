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
        'name',
        'description',
        'category_id',
        'unit_id',
        'minimum_stock',
        'active',
    ];

    protected $casts = [
        'minimum_stock' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit():BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stocks():HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function getCurrentStockAttribute()
    {
        return $this->stocks()->sum('quantity');
    }

    public function isLowStock(): bool
    {
        return $this->current_stock < $this->minimum_stock;
    }
}
