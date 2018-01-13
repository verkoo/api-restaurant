<?php

namespace App\Http\Controllers;

use App\Entities\Kitchen;
use App\Entities\Product;
use Verkoo\Common\Entities\Brand;
use Verkoo\Common\Entities\Category;
use Verkoo\Common\Entities\Supplier;
use Verkoo\Common\Entities\UnitOfMeasure;
use Illuminate\Support\Facades\File;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Input;
use Illuminate\Database\QueryException;

class ProductsController extends Controller
{
    public function index()
    {
        $search = request('product');
        $categories = Category::all()->pluck('name', 'id')->toArray();

        $products = Product::orderByCategory(Input::get('category'))
            ->when($search, function ($query) use ($search) {
                return $query->where('products.name', 'LIKE', "%$search%");
            })
            ->paginate();

        return view('products.index', compact('categories','products'));
    }
    
    public function create()
    {
        $categories = Category::all()->pluck('name', 'id')->toArray();
        $suppliers = Supplier::all()->pluck('name', 'id')->toArray();
        $units_of_measure = UnitOfMeasure::all()->pluck('name', 'id')->toArray();
        $brands = Brand::all()->pluck('name', 'id')->toArray();
        $kitchens = Kitchen::all()->pluck('name', 'id')->toArray();
        $product = new Product();
        
        return view('products.create', compact('product', 'categories', 'suppliers', 'brands', 'kitchens', 'units_of_measure'));
    }

    public function store(ProductRequest $request)
    {
        $product = new Product($request->all());

        if ($request->generate_barcode) {
            $product->ean13 = Product::generateBarcode($request->supplier_id, $request->category_id);
        }

        $product->save();

        return redirect('products')->withMessage('Producto añadido con éxito');
    }

    public function edit (Product $product)
    {
        $categories = Category::all()->pluck('name', 'id')->toArray();
        $suppliers = Supplier::all()->pluck('name', 'id')->toArray();
        $units_of_measure = UnitOfMeasure::all()->pluck('name', 'id')->toArray();
        $brands = Brand::all()->pluck('name', 'id')->toArray();
        $kitchens = Kitchen::all()->pluck('name', 'id')->toArray();

        return view('products.edit', compact('product','categories', 'suppliers', 'brands', 'kitchens', 'units_of_measure'));
    }

    public function update (Product $product, ProductRequest $request)
    {
        $product->fill($request->all());

        if ($request->has('delete_photo')) {
            File::delete(storage_path() . '/app/public/' . $product->photo);
            $product->photo = null;
        }

        if ($request->generate_barcode) {
            $product->ean13 = Product::generateBarcode($request->supplier_id, $request->category_id);
        }

        $product->save();

        return redirect('products')->withMessage('Producto actualiado con éxito');
    }

    public function destroy (Product $product)
    {
        try {
            $product->delete();
        } catch ( QueryException $e) {
            return redirect("products/{$product->id}/edit")->withErrors([
                'error' => 'No es posible eliminar el regitro. Es posible que tenga pedidos asociados.'
            ]);
        }

        return redirect('products')->withMessage('Producto eliminado con éxito');
    }

    public function allergens(Product $product)
    {
        $product->load('allergens');
        return view('products.allergens', compact('product'));
    }

    public function customers(Product $product)
    {
        $product->load('customers');
        return view('products.customers', compact('product'));
    }

    public function extras(Product $product)
    {
        $product->load('extras');
        return view('products.extras', compact('product'));
    }
}
