<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['name'];
    
    public function tables() 
    {
        return $this->hasMany(Table::class);
    }
}
