<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}