<?php

namespace App\Http\Controllers;


use App\Entities\Allergen;
use App\Entities\Customer;
use App\Entities\Product;

class ReportsController extends Controller
{
    public function allergens()
    {
        $allergens = Allergen::all();
        $products = Product::all();
        $products->load('allergens');
        $products = $products->chunk(20);

        $pdf = \PDF::loadView('pdf.allergens', compact('allergens', 'products'));
        return $pdf->setOrientation('landscape')->download('allergens.pdf');
    }
}
