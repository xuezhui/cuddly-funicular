<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

class TempPhone extends Model
{
    protected $table = 'smsverify';
    protected $primaryKey = 'id';
}
