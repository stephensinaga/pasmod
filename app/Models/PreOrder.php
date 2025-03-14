<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrder extends Model
{
    use HasFactory;

    public function poItems()
    {
        return $this->hasMany(PreOrderItem::class, 'pre_order_id');
    }
}
