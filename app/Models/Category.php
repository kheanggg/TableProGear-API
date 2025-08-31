<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Category extends Model
{
    protected $primaryKey = 'category_id';
    protected $fillable = ['name', 'description'];
    public $timestamps = false; 

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }
}