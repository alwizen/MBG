<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(MenuDetail::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}