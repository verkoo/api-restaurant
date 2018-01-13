<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class DishMenu extends Model
{
    protected $table = 'dish_menu';
    
    public $timestamps = false;

    protected $fillable = ['dish_id'];

    protected $with = ['products', 'dish'];

    public function dish()
    {
        return $this->hasOne(Dish::class, 'id', 'dish_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'dish_menu_product', 'dish_menu_id');
    }
}
