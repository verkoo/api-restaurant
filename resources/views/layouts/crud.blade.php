@extends('app')

@section('content')
    <div class="container">
        @include('partials.messages')
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    @yield('crud_content')
                </div>
            </div>
        </div>
    </div>
@endsection
