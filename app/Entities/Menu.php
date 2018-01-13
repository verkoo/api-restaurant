<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name', 'salad', 'bread', 'active', 'description', 'tax_id'];

    public function combinations()
    {
        return $this->belongsToMany(Combination::class)->withPivot('price');
    }
    
    public function dishes() 
    {
        return $this->hasMany(DishMenu::class);
    }

    public function tax() {
        return $this->belongsTo(\Verkoo\Common\Entities\Tax::class);
    }

    public function addCombinationWithPrice($combination, $price)
    {
        if ($combination instanceof Combination) {
            $combination = $combination->id;
        }

        $this->combinations()->sync([$combination => ['price' => toCents($price)]], false);
    }
}
