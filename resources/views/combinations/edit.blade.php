@extends('layouts.crud_edit', [
    'name' => 'Combinación',
    'route' => 'combinations',
    'item' => $combination
])

@section('extra_content')
    <div class="section">
        <div class="container">
            <section class="box">
                <form method="POST" action="/combinations/{{ $combination->id }}/dishes">
                    {{ csrf_field() }}
                    <div class="columns">
                        <div class="column">
                            <span class="select">
                                {!! Form::select('dish_id', [ '' => 'Seleccione un plato' ] + $dishes) !!}
                            </span>
                        </div>
                        <div class="column">
                            <input type="text" class="input" name="quantity" placeholder="Introduzca cantidad">
                        </div>
                        <div class="column">
                            <button type="submit" class="button is-info">
                                Añadir Plato
                            </button>
                        </div>
                    </div>
                </form>

                @foreach($combination->dishes as $dish)
                    <div class="columns">
                        <div class="column">
                            {{ $dish->name }}
                        </div>
                        <div class="column">
                            {{ $dish->pivot->quantity }}
                        </div>
                        <div class="column">
                            <form method="POST" action="/combinations/{{ $combination->id }}/dishes/{{ $dish->id }}">
                                {{ method_field('DELETE') }}
                                {{ csrf_field() }}
                                <button type="submit" id="delete_button" class="fa fa-trash button is-small is-danger"></button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </section>
        </div>
    </div>
@endsection