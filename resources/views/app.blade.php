<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta id="token" name="token" value="{{ csrf_token() }}">
    <title>{{ \Verkoo\Common\Entities\Settings::get('company_name') }}</title>

    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
<script>
  window.Laravel = <?php echo json_encode([
        'csrfToken' => csrf_token(),
    ]); ?>
</script>
    <div id="app" class="page-container">
        <div class="sidebar-container">
            <div class="logo has-text-centered">
                <a href="/">{{ \Verkoo\Common\Entities\Settings::get('company_name') }}</a>
            </div>
            <div class="sidebar-wrapper">
                <main-menu request-url="{{ Request::path() }}"></main-menu>
            </div>

        </div>
        <div class="main-container">
            <div class="top-navbar-container">
                @include('partials.nav')
            </div>
            <div class="wrapper-container">
                @yield('content')
            </div>
        </div>
    </div>

@if(config('verkoo.hot_reload'))
    <script src="http://localhost:8080/main.js"></script>
@else
    <script src="{{ asset('js/main.js') }}"></script>
@endif
@yield('scripts')

</body>
</html>
