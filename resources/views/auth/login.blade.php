@extends('layouts/login')
@section('content')
<div class="login-container">
    <div class="login-form">
        <div class="has-text-centered has-padding">
            <h1 class="title">Bienvenido</h1>
        </div>
        <form id="login-form" method="POST" action="{{ url('/login') }}">
            {!! csrf_field() !!}
            <input value="{{ old('username') }}" name="username" class="input" placeholder="usuario" autofocus>
            <span class="help-block">
                    <strong>{{ $errors->first('username') }}</strong>
                </span>
            <input name="password"  type="password" class="input disable" placeholder="contraseña">
            @if ($errors->has('password'))
                <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
            @endif
            <div class="has-text-centered has-padding">
                <button class="button button-block has-text-centered" type="submit">Entrar</button>
            </div>
        </form>
        <div class="form-footer">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <i class="fa fa-lock"></i>
                    <a href="{{ url('/password/reset') }}"> ¿Olvidó su contraseña? </a>
                </div>
            </div>
        </div>
</div>
@endsection