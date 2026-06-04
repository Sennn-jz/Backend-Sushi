<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'menu_id',
        'quantity',
        'notes'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}