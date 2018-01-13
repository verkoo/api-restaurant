<?php

namespace App\Http\Controllers\Api;

use App\Entities\Product;

class ProductCustomersController extends ApiController
{
    public function store(Product $product)
    {
        try {
            $product->customers()->attach(request('customer_id'), [
                'price' => toCents(request('price')),
            ]);
        } catch (\Exception $e) {
            return $this->setStatusCode(422)->respondWithError('Cliente ya existente en el producto.');
        }

        return $product->customers()->where('customer_id', request('customer_id'))->first();
    }

    public function show(Product $product, $customerId)
    {
        $price = $product->getPriceFor($customerId);

        return $this->respond([
            'product_id' => $product->id,
            'price' => $price,
            'cost' => $product->cost,
        ]);
    }

    public function destroy(Product $product, $customer)
    {
        $product->customers()->detach($customer);
    }
}
