<nav class="navbar top-navbar">
    <div id="navMenuExample" class="navbar-menu">
        <div class="navbar-start">
            <div class="navbar-item">
                <p class="top-navbar-user">{{ Auth::user()->name }}</p>
            </div>
            <today-event-notifications></today-event-notifications>
            <next-event-notifications></next-event-notifications>
        </div>

        <div class="navbar-end">
            @can('update_settings')
                <a href="/options" class="button is-primary fa fa-cog fa-2x"></a>
            @endcan
            <form action="/logout" method="POST">
                {{ csrf_field() }}
                <button class="button is-primary fa fa-sign-out fa-2x"></button>
            </form>
        </div>
    </div>
</nav>