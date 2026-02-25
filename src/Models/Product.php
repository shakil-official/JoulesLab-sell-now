<?php

namespace SellNow\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends EloquentModel
{
    protected $table = 'products';

    public $timestamps = false;
    protected $fillable = ['product_id','title', 'slug', 'price', 'user_id', 'image_path', 'file_path'];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
