<?php

namespace App\Entities;

use Verkoo\Common\Entities\Product as CommonProduct;

class Product extends CommonProduct
{
    public function allergens()
    {
        return $this->belongsToMany(Allergen::class);
    }

    public function extras()
    {
        return $this->belongsToMany(Extra::class);
    }

    public function getQuantityAttribute()
    {
        return 1;
    }

    public function getProductIdAttribute()
    {
        return $this->id;
    }

    public function getProductNameAttribute()
    {
        return $this->name;
    }

    public function setKitchenIdAttribute($value)
    {
        if (!$value) {
            $this->attributes['kitchen_id'] = null;
        } else {
            $this->attributes['kitchen_id'] = $value;
        }
    }

    public function hasAllergen($allergen)
    {
        return $this->allergens->contains($allergen);
    }
}
