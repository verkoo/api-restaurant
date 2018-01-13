<?php

namespace App\Http\Controllers\Api;

use App\Entities\DishMenu;

use App\Entities\Product;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MenuDishProductsController extends Controller
{
    public function store(DishMenu $dishMenu, Request $request)
    {
        $dishMenu->products()->sync([$request->product],false);

        $dishMenu->load('products');

        return response()->json($dishMenu->products);
    }

    public function destroy(DishMenu $dishMenu, $product)
    {
        $dishMenu->products()->detach($product);

        $dishMenu->load('products');

        return response()->json($dishMenu->products);
    }
}
