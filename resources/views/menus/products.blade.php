@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                    <menu-products :menu="{{ $menu }}"></menu-products>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if(app()->environment('production', 'staging'))
        <script src="{{ asset('js/main.js') }}"></script>
    @else
        <script src="http://localhost:8080/dist/main.js"></script>
    @endif
@endsection