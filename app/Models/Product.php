<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'product_code',
        'product_images',
        'product_category',
        'product_price',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
