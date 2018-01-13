@extends('layouts.crud_edit', [
    'name' => 'Menú',
    'route' => 'menus',
    'item' => $menu
])


@section('extra_content')
    <div class="section">
        <div class="container">
            <section class="box">
                {!! Form::open(['url' => ["/menus/{$menu->id}/combinations"], 'method' => 'post']) !!}
                <h1 class="title">
                    Combinaciones
                </h1>
                <div class="columns">
                    <div class="column">
                        <span class="select">
                        {!! Form::select('combination_id', [ '' => 'Seleccione una combinación' ] + $combinations) !!}
                    </span>
                    </div>
                    <div class="column">
                        <input type="text" class="input" name="price" placeholder="Introduzca un precio">

                    </div>
                    <div class="column">
                        <button id="add_combination" type="submit" class="button is-info">
                            Añadir Combinación
                        </button>
                    </div>
                </div>
                <h2 class="subtitle has-padding-10">



                </h2>
                {!! Form::close() !!}
            @if($menu->combinations->count())
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Combinación</th>
                            <th>Precio</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($menu->combinations as $combination)
                            <tr>
                                <td>{{ $combination->name }}</td>
                                <td>{{ $combination->price }}</td>
                                <td class="is-icon">
                                    {!! Form::open(['url' => "menus/{$menu->id}/combinations/{$combination->id}", 'method' => 'DELETE']) !!}
                                    <button type="submit" id="delete_button" class="fa fa-trash button is-small is-danger"></button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif

            </section>
        </div>
    </div>


    {{--<div class="col-md-12" style="padding-top: 20px">--}}
        {{--<div class="panel panel-default">--}}
            {{--<div class="panel-heading">Combinaciones</div>--}}
            {{--<div class="panel-body">--}}
                {{--{!! Form::open(['url' => ["/menus/{$menu->id}/combinations"], 'method' => 'post']) !!}--}}
                {{--<div class="col-md-5">--}}
                    {{--{!! Form::select('combination_id', [ '' => 'Seleccione una combinación' ] + $combinations, null, ['class' => 'form-control']) !!}--}}
                {{--</div>--}}
                {{--<div class="col-md-4"><input type="text" class="form-control" name="price" placeholder="Introduzca un precio"></div>--}}
                {{--<div class="col-md-3">--}}
                    {{--<button id="add_combination" type="submit" class="btn btn-xs btn-info">--}}
                        {{--Añadir Combinación--}}
                    {{--</button>--}}
                {{--</div>--}}
                {{--{!! Form::close() !!}--}}

                {{--@if($menu->combinations->count())--}}
                    {{--<table class="table">--}}
                        {{--<thead>--}}
                        {{--<tr>--}}
                            {{--<th>Combinación</th>--}}
                            {{--<th>Precio</th>--}}
                            {{--<th></th>--}}
                        {{--</tr>--}}
                        {{--</thead>--}}
                        {{--<tbody>--}}
                        {{--@foreach($menu->combinations as $combination)--}}
                            {{--<tr>--}}
                                {{--<td>{{ $combination->name }}</td>--}}
                                {{--<td>{{ $combination->price }}</td>--}}
                                {{--<td class="is-icon">--}}
                                    {{--{!! Form::open(['url' => "menus/{$menu->id}/combinations/{$combination->id}", 'method' => 'DELETE']) !!}--}}
                                    {{--<button type="submit" id="delete_button" class="fa fa-trash btn btn-xs btn-danger"></button>--}}
                                    {{--{!! Form::close() !!}--}}
                                {{--</td>--}}
                            {{--</tr>--}}
                        {{--@endforeach--}}
                        {{--</tbody>--}}
                    {{--</table>--}}
                {{--@endif--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}

@endsection