@extends('app')

@section('content')

<section class="hero is-primary">
    <div class="hero-body">
        <div class="container">
            <h1 class="title">
                Crear {{ $name }}
            </h1>
        </div>
    </div>
</section>

@include('partials.messages')

<div class="container">
    {!! Form::open(['id' => 'form', 'url' => [$route], 'method' => 'POST', 'files' => isset($files) ? $files : false ]) !!}
    <div class="section">
        <div class="box">
            @include("$route.partials.fields")
        </div>
    </div>
    <button type="submit" class="button is-warning crud-button">Crear {{ $name }}</button>
    {!! Form::close() !!}
    <a href="/{{ $route }}" class="button is-info crud-button" id="back_link">Volver</a>
</div>
@endsection
