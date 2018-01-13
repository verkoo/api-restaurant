<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $product = $this->route('product');

        return [
            'name' => 'required',
            'ean13' => Rule::unique('products')->ignore($product ? $product->id : ''),
            'category_id' => 'required'
        ];
    }

    public function all()
    {
        $attributes = parent::all();

        if ( ! $this->has('unit_of_measure_id')) {
            $attributes["unit_of_measure_id"] = null;
        }

        if ( ! $this->has('stock_control')) {
            $attributes["stock_control"] = 0;
        }

        if ( ! $this->has('active')) {
            $attributes["active"] = 0;
        }
        
        if ($this->hasFile('photo')) {
            $attributes["photo"] = $this->file('photo')->store('img', 'public');
        }

        return $attributes;
    }
}
