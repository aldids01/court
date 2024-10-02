<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function order():BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function template():BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
    protected $casts = [
        'content' => 'array', // This will cast the content to/from JSON automatically
    ];


}
