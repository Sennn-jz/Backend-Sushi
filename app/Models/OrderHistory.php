<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class OrderHistory extends Model
{
    protected $fillable = [
        'order_id',
        'old_status',
        'new_status',
        'updated_by',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
