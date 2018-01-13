<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = ['name', 'zone_id'];
    protected $appends = ['order_id', 'order_number', 'customer'];

    public static function isOccupied($table_id)
    {
        if (! $table_id) {
            return false;
        }

        $table = (new static)::findOrFail($table_id);

        return $table->hasOpenOrder();
    }

    public function hasOpenOrder() 
    {
        if (! $this->order) {
            return false;
        }
        
        return ! $this->order->cashed;
    }
    
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
    
    public function order() {
        return $this->hasOne(Order::class);
    }
    
    public function getOrderIdAttribute() 
    {
        return $this->order ? $this->order->id : false;
    }

    public function getOrderNumberAttribute()
    {
        return $this->order ? $this->order->number : false;
    }

    public function getCustomerAttribute()
    {
        return $this->order ? $this->order->customer_name : '';
    }
}
