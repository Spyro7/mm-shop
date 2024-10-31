<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $guarded = false;

    protected $table = 'brands';

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
