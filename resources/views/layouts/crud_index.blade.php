@extends('app')

@section('content')
    <section class="hero is-primary">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">
                    {{ $name }}
                </h1>
                <h2 class="subtitle has-padding-10">
                    <a id="new_link" class="button is-dark " href="{{ url("$route/create") }}">
                        {{ $button }}
                    </a>
                </h2>
            </div>
        </div>
    </section>

    @include('partials.messages')

    <div class="container">

        @if(isset($filter) && $filter)
            <div class="box">
                {!! Form::open(['url' => $route, 'method' => 'GET']) !!}
                <div class="columns">
                    <div class="column">
                        <p class="control">
                            {!! Form::text('search', Request::get('search'), ['placeholder' => 'Nombre', 'class' => 'input']) !!}
                        </p>
                    </div>
                    <div class="column">
                        <button type="submit" class="button is-success">Filtrar</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        @endif

        @include("$route.partials.table")

        @if(isset($filter))
            {!! $items->appends(Request::only($filter['field']))->render() !!}
        @else
        {!! $items->render() !!}
        @endif

    </div>
@endsection
