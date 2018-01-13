<?php

namespace App\Http\ViewComposers;

use App\Entities\Product;
use Illuminate\Contracts\View\View;

class LineComposer
{
    public function compose(View $view)
    {
        $products = Product::all()->pluck('name', 'id');
        $view->with(['products' => $products, 'route' => getMainRoute()]);
    }
}