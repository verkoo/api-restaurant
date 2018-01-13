@extends('app')

@section('content')
    <allergens
            :product="{{ $product }}"
    ></allergens>
@endsection