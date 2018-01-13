<?php

namespace App\Http\Controllers\Api;

use App\Entities\Product;
use Verkoo\Common\Entities\Category;
use Verkoo\Common\Entities\Settings;
use Illuminate\Support\Facades\Input;

class CategoryProductsController extends ApiController
{
    public function index(Category $category)
    {
        $limit = Input::get('limit') ?: Settings::get('pagination');
        $orderBy = Input::get('orderBy') ?? 'name';
        
        $products = Product::with('category')
            ->active()
            ->withStock()
            ->categoryOrChildren($category)
            ->orderBy($orderBy)
            ->paginate($limit);

        $products->load('extras');

        return $this->respondWithPagination($products, [ 'data' => $products->all() ]);
    }
}
