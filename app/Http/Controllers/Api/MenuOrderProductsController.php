<?php

namespace App\Http\Controllers\Api;

use App\Entities\MenuOrder;
use Illuminate\Http\Request;

class MenuOrderProductsController extends ApiController
{
    public function index(MenuOrder $menuOrder)
    {
        $dishes = $menuOrder->menu->dishes->sortBy('dish.priority');
        $dishes = $dishes->values()->all();

        $products = $menuOrder->products;

        $combinations = $menuOrder->getAvailableCombinations();

        return $this->respond(compact('dishes', 'products','combinations'));
    }

    public function store(MenuOrder $menuOrder, Request $request)
    {
        try {
            $combinations = $menuOrder->addProduct($request->all());
        } catch (\Exception $e) {
            return $this->setStatusCode(422)->respondWithError($e->getMessage());
        }

        return $this->setStatusCode(201)->respond([
            'price' => number_format($combinations->first()->price / 100,2,',','.'),
            'combinations' => $combinations
        ]);
    }

    public function destroy(MenuOrder $menuOrder, $product)
    {
        $combinations = $menuOrder->deleteProduct($product);

        if ($menuOrder->products->isEmpty()) {
            $price = '0,00';
        } else {
            $price = number_format($combinations->first()->price / 100,2,',','.');
        }

        return $this->respond(compact('price', 'combinations'));
    }
}
