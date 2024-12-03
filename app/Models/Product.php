<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = false;
    protected $casts = [
        'images' => 'array',
        'specifications' => 'array',
        'color' => 'array',
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function brand(){
        return $this->belongsTo(Brand::class);
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }
    public function reviews(){
        return $this->hasMany(ProductReview::class);
    }
}
