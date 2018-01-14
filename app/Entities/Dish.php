<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    protected $fillable = ['name', 'priority', 'pass'];

    protected $appends = ['quantity'];

    public function getQuantityAttribute()
    {
        return $this->pivot ? $this->pivot->quantity: 0;
    }
}
