@extends('app')

@section('content')
    <section class="hero is-primary">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">
                    Editar {{ $name }}
                </h1>
            </div>
        </div>
    </section>

    @include('partials.messages')

    <div class="container">
        {!! Form::model($item, [ 'id' => 'form', 'url' => ["{$route}/{$item->id}"], 'method' => 'PUT', 'files' => isset($files) ? $files : false ]) !!}
        <div class="section">
            <div class="box">
                @include("$route.partials.fields")
            </div>
        </div>
        <button type="submit" class="button is-warning crud-button">Editar {{ $name }}</button>
        {!! Form::close() !!}
        @unless(isset($hideDeleteButton))
            <form action="{{ url("$route/{$item->id}") }}" method="POST">
                {{ method_field('DELETE') }}
                {{ csrf_field() }}
                <button type="submit" class="button is-danger crud-button" id="delete_button">Eliminar {{ $name }}</button>
            </form>
        @endunless

        <a href="/{{ $route }}" class="button is-info crud-button" id="back_link">Volver</a>
    </div>
    @yield('extra_content')
@endsection
