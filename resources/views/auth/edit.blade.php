@extends('app')

@section('content')
    <section class="hero is-primary">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">
                    Editar Usuario
                </h1>
            </div>
        </div>
    </section>

    @include('partials.messages')

    <div class="container">
        {!! Form::model($user, [ 'id' => 'form', 'url' => ["users/{$user->id}"], 'method' => 'PUT']) !!}
        <div class="section">
            <div class="box">
                @include('auth/partials/fields')

            </div>
        </div>
        <button type="submit" class="button is-warning crud-button">
            Editar Usuario
        </button>
        {!! Form::close() !!}

        @can('delete', Auth::user())
            {!! Form::open(['url' => ["users/{$user->id}"], 'method' => 'DELETE']) !!}
            <button type="submit" class="button is-danger crud-button">Eliminar Usuario</button>
            {!! Form::close() !!}
        @endcan

        <a href="/users" class="button is-info crud-button" id="back_link">Volver</a>
    </div>
@endsection