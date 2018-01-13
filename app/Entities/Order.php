<?php

namespace App\Entities;

use Verkoo\Common\Entities\Order as CommonOrder;
use Verkoo\Common\Events\OrderCashed;
use Illuminate\Support\Facades\DB;

class Order extends CommonOrder
{
    protected $with = ['lines', 'menus'];

    public function getMorphClass()
    {
        return CommonOrder::class;
    }

    public function lines()
    {
        return $this->morphMany(Line::class, 'lineable');
    }

    public function menus()
    {
        return $this->hasMany(MenuOrder::class);
    }
    
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function getTaxesAttribute()
    {
        $all = $this->lines->merge($this->menus);

        $taxes = $all->groupBy('vat')->map(function ($item) {
            $totalVat = $item->sum(function ($line) {
                $taxRate = $line->vat / 100;
                $price = $line->getOriginal('price') / 100;
                $quantity = $line->quantity ?: 1;
                $tax = ($price * $quantity) / (1 + $taxRate) * $taxRate;
                return round($tax,2);
            });

            $totalBase = $item->sum(function ($line) {
                $taxRate = $line->vat / 100;
                $quantity = $line->quantity ?: 1;
                $price = $line->getOriginal('price') / 100;
                $total = ($price * $quantity) / (1 + $taxRate);
                return round($total,2);
            });

            return [
                'base' => number_format($totalBase, 2, ',','.'),
                'vat' => number_format($totalVat, 2, ',','.'),
            ];
        });

        return $taxes->all();
    }

    public function notOrderedProducts()
    {
        $products = DB::table('menu_order_product')
            ->join('products', 'menu_order_product.product_id', '=', 'products.id')
            ->join('menu_orders', 'menu_order_product.menu_order_id', '=', 'menu_orders.id')
            ->join('kitchens', 'menu_order_product.kitchen_id', '=', 'kitchens.id')
            ->join('orders', 'menu_orders.order_id', '=', 'orders.id')
            ->where('orders.id', $this->id)
            ->where('menu_order_product.ordered', 0)
            ->whereNotNull('menu_order_product.kitchen_id')
            ->select('products.name as product_name', 'kitchens.printer', 'menu_order_product.kitchen_id', DB::raw('1 as remaining'))
            ->get();

        return $products;
    }

    public function notOrderedLines()
    {
        $products = DB::table('lines')
            ->join('orders', function ($join) {
                $join->on('lines.lineable_id', '=', 'orders.id')
                    ->where('lines.lineable_type', '=', (new Order())->getMorphClass());
            })
            ->join('kitchens', 'lines.kitchen_id', '=', 'kitchens.id')
            ->where('orders.id', $this->id)
            ->where(DB::raw('lines.quantity - lines.ordered'), '>', 0)
            ->whereNotNull('lines.kitchen_id')
            ->select('lines.product_name', 'lines.parent', 'kitchens.printer', 'lines.kitchen_id', DB::raw('lines.quantity - lines.ordered as remaining'))
            ->get();

        return $products;
    }

    public function getNewOrderedLines()
    {
        return $this->lines()->where('quantity' > DB::raw('ordered'));
    }
    
    public function markNotServedLinesAsServed()
    {
        $this->lines()->update(['ordered' => DB::raw('quantity')]);

        $this->menus->each(function($menu) {
            $menu->products()->update(['ordered' => 1]);
        });
    }

    public function discardNotServedLines()
    {
        $this->lines()->withKitchen()->update(['quantity' => DB::raw('ordered')]);
        Line::deleteIfZeroQuantity();
    }

    public function markAsCashed($payment, $amount, $diners = 1)
    {
        $this->payment_id = $payment;
        $this->cashed_amount = $amount;
        $this->table_id = null;
        $this->save();
        event(new OrderCashed($this, $diners));
    }

    protected function getTotal()
    {
        $total = 0;

        foreach ($this->lines as $line) {
            $total += $line->quantity * (float) str_replace(',','.',$line->price);
        }
        foreach ($this->menus as $menu) {
            $total += str_replace(',','.',$menu->price);
        }

        return $total - $this->discount;
    }
}
