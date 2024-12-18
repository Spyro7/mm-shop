<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = false;
    protected $table = 'orders';

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items(){
        return $this->hasMany(OrderItem::class);
    }

    public function payment(){
        return $this->hasOne(Payment::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }
}
