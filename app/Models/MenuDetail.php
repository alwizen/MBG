<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_menu_id',
        'item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}