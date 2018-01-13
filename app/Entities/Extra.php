<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Verkoo\Common\Traits\Priceable;

class Extra extends Model
{
    use Priceable;
    
    protected $fillable = ['name', 'price'];
}
