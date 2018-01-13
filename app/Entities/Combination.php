<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Combination extends Model
{
    protected $fillable = ['name'];

    public function dishes()
    {
        return $this->belongsToMany(Dish::class)->withPivot('quantity');
    }
    
    public function getPriceAttribute() 
    {
        return number_format($this->pivot->price / 100,2,',','');
    }

    public function addDish($dish, $quantity)
    {
        if ($dish instanceof Dish) {
            $dish = $dish->id;
        }

        $this->dishes()->sync([$dish => ['quantity' => $quantity]], false);
    }
}
