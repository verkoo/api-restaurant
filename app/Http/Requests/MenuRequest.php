<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
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
        return [
            'name' => 'required'
        ];
    }

    public function all()
    {
        $attributes = parent::all();

        if ( ! $this->has('salad')) {
            $attributes["salad"] = 0;
        }

        if ( ! $this->has('bread')) {
            $attributes["bread"] = 0;
        }

        if ( ! $this->has('active')) {
            $attributes["active"] = 0;
        }

        if (! $this->has('tax_id')) {
            $attributes["tax_id"] = null;
        }

        return $attributes;
    }
}
