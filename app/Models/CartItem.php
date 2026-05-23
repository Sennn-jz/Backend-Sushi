<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    // Cukup tambahkan 'notes' di dalam array fillable ini
    protected $fillable = ['cart_id', 'menu_id', 'quantity', 'notes'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}