<?php

namespace App\Entities;

use App\Exceptions\SameProductInMenuException;
use Verkoo\Common\Entities\Options;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\CombinationNotAllowed;
use App\Exceptions\ProductNotInMenuException;

class MenuOrder extends Model
{
    protected $fillable = ['order_id', 'menu_id', 'name', 'price'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $appends = ['vat'];

    protected $with = ['products'];

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($menuOrder) {
            foreach ($menuOrder->products as $product) {
                $product->increaseStock(1);
            }
        });
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class,'menu_order_product','menu_order_id')->withPivot('dish_id', 'kitchen_id', 'ordered');
    }

    public function dishes()
    {
        return $this->belongsToMany(Dish::class,'menu_order_product','menu_order_id');
    }

    public function getVatAttribute() {
        if ($this->menu->tax) {
            return $this->menu->tax->percentage;
        }

        $options = Options::first();

        if ($options &&  $tax = $options->tax) {
            return $tax->percentage;
        }
    }

    public function getPriceAttribute()
    {
        return number_format($this->attributes['price'] / 100,2,',','');
    }

    public function getAvailableCombinations()
    {
        $combinations = $this->availableCombinations($this->getCombinations());
        return $combinations->flatten();
    }

    protected function updatePrice()
    {
        $combinations = $this->availableCombinations($this->getCombinations());
        $combinations = $combinations->flatten();

        $this->load('products');

        if ($this->products->isEmpty()) {
            $price = 0;
        } else {
            $price = $combinations->first()->price;
        }

        $this->price = $price;
        $this->save();

        return $combinations;
    }

    public function addProduct($attributes)
    {
        $productId = $attributes['product_id'];

        if (! $this->belongsToMenu($productId, $attributes['dish_id'])) {
            throw new ProductNotInMenuException;
        }

        $attached = $this->products()->sync([
            $productId => [
                'dish_id' => $attributes['dish_id'],
                'kitchen_id' => array_key_exists('kitchen_id',$attributes) ? $attributes['kitchen_id'] : null,
            ]
        ], false);

        if(empty($attached['attached'])) {
            throw new SameProductInMenuException;
        }

        Product::find($productId)->reduceStock(1);

        try {
            return $this->updatePrice();
        } catch(CombinationNotAllowed $e) {
            $this->deleteProduct($productId);
            throw new CombinationNotAllowed;
        }
    }

    public function deleteProduct($product)
    {
        $this->products()->detach($product);

        Product::find($product)->increaseStock(1);

        return $this->updatePrice();
    }

    public function belongsToMenu($product, $dish)
    {
        $product = \DB::table('dish_menu_product')
            ->join('dish_menu', 'dish_menu_product.dish_menu_id', '=', 'dish_menu.id')
            ->select('dish_menu.id')
            ->where('dish_id', $dish)
            ->where('product_id', $product)
            ->where('menu_id', $this->menu_id)
            ->get();

        return ! $product->isEmpty();
    }

    public function getCombinations()
    {
        $combinations = \DB::table('combination_dish')
            ->join('combination_menu', 'combination_dish.combination_id', '=', 'combination_menu.combination_id')
            ->select('combination_dish.*', 'combination_menu.price')
            ->where('menu_id', $this->menu_id)
            ->orderBy('combination_menu.price', 'ASC')
            ->get();

        return $combinations->isEmpty() ? null : $combinations;
    }

    public function availableCombinations($combinations)
    {
        if (! $combinations) {
            throw new CombinationNotAllowed;
        }

        $this->load('dishes');
        $dishes = $this->dishes->groupBy('pivot.dish_id');
        $combinations = $combinations->groupBy('combination_id');

        $filtered = $combinations->filter(function($combination) use ($dishes) {
            return $dishes->every(function ($dish) use ($combination) {
                $total = count($dish);
                $dish_id = $dish->first()->pivot->dish_id;

                return $combination->contains(function ($c) use ($total, $dish_id) {
                    return $c->quantity >= $total && $c->dish_id === $dish_id;
                });
            });
        });

        if ($filtered->isEmpty()) {
            throw new CombinationNotAllowed;
        }

        return $filtered;
    }
}
