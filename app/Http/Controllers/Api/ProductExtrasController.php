<?php

namespace App\Http\Controllers\Api;

use App\Entities\Extra;
use App\Entities\Product;

class ProductExtrasController extends ApiController
{
    public function index()
    {
        return Extra::orderBy('name')->get();
    }

    public function store(Product $product)
    {
        try {
            $product->extras()->attach(request('extra_id'));
        } catch (\Exception $e) {
            return $this->setStatusCode(422)->respondWithError('Extra ya existente en el producto.');
        }

        $extra = Extra::findOrFail(request('extra_id'));
        return $extra;
    }

    public function destroy(Product $product, $extra)
    {
        $product->extras()->detach($extra);
    }
}
