<?php

namespace App\Http\ViewComposers;

use App\Entities\Customer;
use Illuminate\Contracts\View\View;

class DocumentComposer
{
    public function compose(View $view)
    {
        $customers = Customer::all()->pluck('name', 'id');
        $view->with(compact('customers'));
    }
}