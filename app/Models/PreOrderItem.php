<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrderItem extends Model
{
    use HasFactory;

    public function preorder()
    {
        return $this->belongsTo( PreOrder::class, 'pre_order_id');
    }
}
