{{--<li><a {{{ (Request::is('partes') ? 'class=active-menu' : '') }}} href="{{ url('/partes') }}"><i class="fa fa-newspaper-o fa-3x"></i> Partes</a></li>--}}
{{--<li><a href="{{ url('/') }}"><i class="fa fa-tasks fa-3x"></i> Tareas</a></li>--}}
{{--<li>--}}
    {{--<a {{{ (Request::is('dashboard') ? 'class="active-menu"' : '') }}} href="/dashboard"><i class="fa fa-dashboard fa-3x"></i> Dashboard</a>--}}
{{--</li>--}}
<li><a href="#"><i class="fa fa-money fa-3x"></i> Facturación<span class="fa arrow"></a>
    <ul class="nav nav-second-level">
        <li><a href="{{ url('quotes') }}"><i class="fa fa-sticky-note-o fa-2x"></i>Presupuestos</a></li>
        <li><a href="{{ url('orders') }}"><i class="fa fa-shopping-basket fa-2x"></i>Pedidos</a></li>
        <li><a href="{{ url('delivery-notes') }}"><i class="fa fa-sticky-note fa-2x"></i>Albaranes</a></li>
        <li><a href="{{ url('invoices') }}"><i class="fa fa-wpforms fa-2x"></i>Facturas</a></li>
    </ul>
</li>
<li>
    <a href="#"><i class="fa fa-desktop fa-3x"></i> Punto de Venta<span class="fa arrow"></span></a>
    <ul class="nav nav-second-level">
        <li><a href="/tpv"><i class="fa fa-tablet fa-2x"></i>TPV</a></li>
        <li><a href="{{ url('boxes/sessions') }}"><i class="fa fa-clock-o fa-2x"></i>Sesiones de Caja</a></li>
        <li><a href="{{ url('reports/orders') }}"><i class="fa fa-ticket fa-2x"></i>Reportes de Ventas</a></li>
        <li><a href="{{ url('reports/products') }}" target="_blank"><i class="fa fa-file-pdf-o fa-2x"></i>Imprimir PDF Stock</a></li>
    </ul>
</li>
<li><a {{{ (Request::is('products') ? 'class=active-menu' : '') }}} href="{{ url('products') }}"><i class="fa fa-shopping-bag fa-3x"></i>Productos</a></li>
<li><a {{{ (Request::is('customers') ? 'class=active-menu' : '') }}} href="{{ url('/customers') }}"><i class="fa fa-users fa-3x"></i> Clientes</a></li>
{{--<li><a href="{{ url('/') }}"><i class="fa fa-bar-chart fa-3x"></i> Estadísticas</a></li>--}}

<li>
    <a href="#"><i class="fa fa-database fa-3x"></i> Configuración<span class="fa arrow"></span></a>
    <ul class="nav nav-second-level">
        <li><a href="{{ url('/categories') }}"><i class="fa fa-code-fork fa-2x"></i>Categorías</a></li>
        <li><a href="{{ url('/suppliers') }}"><i class="fa fa-amazon fa-2x"></i>Proveedores</a></li>
        <li><a href="{{ url('/brands') }}"><i class="fa fa-apple fa-2x"></i> Marcas</a></li>
        <li><a href="{{ url('/zones') }}"><i class="fa fa-cube fa-2x"></i>Zonas</a></li>
        <li><a href="{{ url('/tables') }}"><i class="fa fa-table fa-2x"></i>Mesas</a></li>
        <li><a href="{{ url('/kitchens') }}"><i class="fa fa-beer fa-2x"></i>Cocinas</a></li>
        <li><a href="{{ url('/extras') }}"><i class="fa fa-plus-circle fa-2x"></i>Extras de Producto</a></li>
        <li><a href="{{ url('/menus') }}"><i class="fa fa-list-alt fa-2x"></i>Menús</a></li>
        <li><a href="{{ url('/combinations') }}"><i class="fa fa-compress fa-2x"></i>Combinaciones del Menú</a></li>
        <li><a href="{{ url('/dishes') }}"><i class="fa fa-cutlery fa-2x"></i>Platos del Menú</a></li>
        <li><a href="{{ url('/boxes') }}"><i class="fa fa-laptop fa-2x"></i>Cajas</a></li>
        <li><a href="{{ url('/payments') }}"><i class="fa fa-credit-card-alt fa-2x"></i>Formas de Pago</a></li>
        <li><a href="{{ url('/taxes') }}"><i class="fa fa-line-chart fa-2x"></i>Tipos de Iva</a></li>
        {{--<li><a href="{{ url('/tasks') }}"><i class="fa fa-bars fa-2x"></i> Tareas</a></li>--}}
        {{--<li><a href="{{ url('/devices') }}"><i class="fa fa-mobile fa-2x"></i> Dispositivos</a></li>--}}

        {{--<li><a href="{{ url('/models') }}"><i class="fa fa-tags fa-2x"></i> Modelos</a></li>--}}
        {{--<li><a href="{{ url('/accessories') }}"><i class="fa fa-plug fa-2x"></i> Accesorios</a></li>--}}
        {{--<li><a href="{{ url('/conditions') }}"><i class="fa fa-exclamation-triangle fa-2x"></i> Condiciones</a></li>--}}
        {{--<li><a href="{{ url('/departments') }}"><i class="fa fa-object-ungroup fa-2x"></i> Departamentos</a></li>--}}
        {{--<li><a href="{{ url('/cities') }}"><i class="fa fa-building-o fa-2x"></i> Ciudades</a></li>--}}
        <li>
            @can('create_users')
                <a href="/users"><i class="fa fa-male fa-2x"></i>Usuarios</a>
            @endcan
            {{--<ul class="nav nav-third-level">--}}
                {{--<li>--}}
                    {{--<a href="#">Third Level Link</a>--}}
                {{--</li>--}}
                {{--<li>--}}
                    {{--<a href="#">Third Level Link</a>--}}
                {{--</li>--}}
                {{--<li>--}}
                    {{--<a href="#">Third Level Link</a>--}}
                {{--</li>--}}
            {{--</ul>--}}
        </li>
    </ul>
</li>
