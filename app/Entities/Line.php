<?php

namespace App\Entities;

use Verkoo\Common\Entities\Line as CommonLine;

class Line extends CommonLine
{
    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeWithKitchen($query)
    {
        return $query->whereNotNull('kitchen_id');
    }
}
