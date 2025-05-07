<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ItemReturn extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'purchase_id',
        'date',
        'reason',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ItemReturnDetail::class, 'return_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}