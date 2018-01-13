@extends('app')

@section('content')
    <product-customers
            :product="{{ $product }}"
    ></product-customers>
@endsection