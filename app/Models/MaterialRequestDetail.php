<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequestDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_request_id',
        'item_id',
        'quantity',
        'approved_quantity',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'approved_quantity' => 'decimal:2',
    ];

    public function materialRequest(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}