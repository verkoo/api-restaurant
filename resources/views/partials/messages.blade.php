    @if ($errors->any())
        <div class="notification is-danger" role="alert">
            <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    @endif

    @if(Session::has('message'))
        <p class="notification is-success">{{ Session::get('message') }}</p>
    @endif
