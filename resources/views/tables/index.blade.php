@include('layouts.crud_index', [
    'name' => 'Mesas',
    'button' => 'Nueva Mesa',
    'route' => 'tables',
    'items' => $tables,
])
