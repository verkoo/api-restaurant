<?php

namespace App\Http\Controllers\Api;

use App\Entities\Allergen;
use App\Entities\Product;

class ProductAllergensController extends ApiController
{
    protected $productTransformer;

    public function index()
    {
        return Allergen::orderBy('name')->get();
    }

    public function store(Product $product)
    {
        try {
            $product->allergens()->attach(request('allergen_id'));
        } catch (\Exception $e) {
            return $this->setStatusCode(422)->respondWithError('Alérgeno ya existente en el producto.');
        }

        $allergen = Allergen::findOrFail(request('allergen_id'));
        return $allergen;
    }

    public function destroy(Product $product, $allergen)
    {
        $product->allergens()->detach($allergen);
    }
}
