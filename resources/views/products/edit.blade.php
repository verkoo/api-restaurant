@extends('layouts.crud_edit', [
    'name' => 'Producto',
    'route' => 'products',
    'item' => $product,
    'files' => true
])