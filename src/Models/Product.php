<?php

namespace SellNow\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends EloquentModel
{
    protected $table = 'products';
    protected $fillable = ['name', 'price', 'description', 'user_id', 'image_path', 'file_path'];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}