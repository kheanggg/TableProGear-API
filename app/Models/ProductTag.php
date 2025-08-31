<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;
use App\Models\Product;

class ProductTag extends Model
{
    public $timestamps = false; 
    
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tags');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tags');
    }
}