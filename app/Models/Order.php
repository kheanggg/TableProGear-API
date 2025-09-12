<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Table name (optional if following Laravel conventions)
    protected $table = 'orders';

    // Primary key
    protected $primaryKey = 'order_id';

    // Mass assignable fields
    protected $fillable = [
        'customer_id',
        'contact_name',
        'phone_number',
        'address',
        'note',
        'order_date',
        'subtotal',
        'total',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
