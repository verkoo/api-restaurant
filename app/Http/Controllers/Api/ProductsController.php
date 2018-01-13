<?php

namespace App\Http\Controllers\Api;

use App\Entities\Product;
use Verkoo\Common\Entities\Settings;
use Illuminate\Support\Facades\Input;

class ProductsController extends ApiController
{
    public function index()
    {
        if ($search = Input::get('q') ?? false) {
            return Product::active()->where('name', 'LIKE', "%$search%")->get();
        }

        $limit = Input::get('limit') ?: Settings::get('pagination');
        $orderBy = Input::get('orderBy') ?? 'name';

        $products = Product::active()
            ->withStock()
            ->orderBy($orderBy)
            ->paginate($limit);

        $products->load('extras');
        
        return $this->respondWithPagination($products, [
            'data' => $products->all()
        ]);
    }

    public function updateStock()
    {
        $this->validate(request() , [
           'products.*.stock' => 'required|numeric'
        ]);

        Product::query()->update(['priority' => 0]);

        $priority = 1;
        foreach (request('products') as $product) {
            Product::findOrFail($product['id'])
                ->update(['stock' => $product['stock'], 'initial_stock' => $product['stock'], 'priority' => $priority]);
            $priority++;
        }
        return $this->respond(['success' => true]);
    }
}
