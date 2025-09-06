<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Favorite extends Model
{
    protected $table = 'favorites';

    // No timestamps
    public $timestamps = false;

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

}
