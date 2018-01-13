@extends('app')

@section('content')
    <section class="hero is-primary">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">
                    Productos
                </h1>
                <h2 class="subtitle has-padding-10">
                    <a id="new_link" class="button is-dark" href="{{ url('products/create') }}">Nuevo Producto</a>
                </h2>
            </div>
        </div>
    </section>

    @include('partials.messages')

    <div class="container">
        <div class="box">
            {!! Form::open(['url' => 'products', 'method' => 'GET']) !!}
            <div class="columns">
                <div class="column">
                    <p class="control">
                        <span class="select is-fullwidth">
                            {!! Form::select('category', [ '' => 'Categoría' ] + $categories, Request::only(['category'])) !!}
                        </span>
                    </p>
                </div>
                <div class="column">
                    <p class="control">
                        {!! Form::text('product', Request::get('product'), ['placeholder' => 'Producto', 'class' => 'input']) !!}
                    </p>
                </div>
                <div class="column">
                    <button type="submit" class="button is-success">Filtrar</button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>


        @include('products.partials.table')

        {!! $products->appends(Request::only(['category', 'product']))->render() !!}
    </div>
@endsection
