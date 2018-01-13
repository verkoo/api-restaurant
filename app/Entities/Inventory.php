<?php

namespace App\Entities;


class Inventory
{
    public static function resetStockInRecountableCategories()
    {
        $products = Product::withRecountableCategory();

        $products->update(['stock' => 0]);
    }
}