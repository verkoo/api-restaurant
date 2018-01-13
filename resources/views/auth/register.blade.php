@extends('app')

@section('content')
    <section class="hero is-primary">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">
                    Registrar Usuario
                </h1>
            </div>
        </div>
    </section>

    @include('partials.messages')

    <div class="container">
        {!! Form::open(['id' => 'form', 'url' => '/users', 'method' => 'POST']) !!}
        <div class="section">
            <div class="box">
                @include('auth/partials/fields')
            </div>
        </div>
        <button type="submit" class="button is-warning crud-button">Registrar Usuario</button>
        {!! Form::close() !!}
        <a href="/users" class="button is-info crud-button" id="back_link">Volver</a>
    </div>
@endsection