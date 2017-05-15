<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'pro_order_item';
    protected $primaryKey = 'id';

    //public $timestamps = false;
}
