<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function items():HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    protected $casts = [
        'content' => 'array', // This will cast the content to/from JSON automatically
    ];
}
