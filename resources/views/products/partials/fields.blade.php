<p class="control has-top-padding">
    {!! Form::label('name', 'Nombre', ['class' => 'label']) !!}
    {!! Form::text('name', null, ['class' => 'input']) !!}
</p>
<div class="has-padding-10">
    <p class="control has-top-padding">
        {!! Form::checkbox('active', 1, $product->active) !!} Activo
    </p>

    <p class="control has-top-padding">
        {!! Form::checkbox('stock_control', 1, $product->stock_control) !!} Controlar Stock
    </p>
</div>

<p class="control has-top-padding">
    {!! Form::label('stock', 'Stock', ['class' => 'label']) !!}
    {!! Form::text('stock', null, ['class' => 'input']) !!}
</p>

<p class="control has-top-padding">
    {!! Form::label('category_id', 'Categoría', ['class' => 'label']) !!}
    <span class="select">
        {!! Form::select('category_id', $categories) !!}
    </span>
</p>

<p class="control has-top-padding">
    {!! Form::label('photo', 'Foto', ['class' => 'label']) !!}
    @if($product->photo)
        <div class="product-photo-form">
            <img src="/storage/{{ $product->photo }}" width="100px">
        </div>
        <div>
            <label for="delete_photo">Eliminar</label>
            {!! Form::checkbox('delete_photo', 1, 0) !!}
        </div>
    @endif
    {!! Form::file('photo', null, ['class' => 'input']) !!}
</p>

<p class="control has-top-padding">
    {!! Form::label('kitchen_id', 'Cocina', ['class' => 'label']) !!}
    <span class="select">
        {!! Form::select('kitchen_id', [ '' => '(Sin cocina)' ] + $kitchens) !!}
    </span>
</p>

<p class="control has-top-padding">
    {!! Form::label('supplier_id', 'Proveedor', ['class' => 'label']) !!}
    <span class="select">
        {!! Form::select('supplier_id', [ '' => '(Sin Proveedor)' ] + $suppliers) !!}
    </span>
</p>

<p class="control has-top-padding">
    {!! Form::label('brand_id', 'Marca', ['class' => 'label']) !!}
    <span class="select">
        {!! Form::select('brand_id', [ '' => '(Sin Marca)' ] + $brands) !!}
    </span>
</p>

<p class="control has-top-padding">
    {!! Form::label('unit_of_measure_id', 'Unidad de Medida', ['class' => 'label']) !!}
    <span class="select">
        {!! Form::select('unit_of_measure_id', [ '' => '(Sin Unidad de Medida)' ] + $units_of_measure) !!}
    </span>
</p>

<p class="control has-top-padding">
    {!! Form::label('price', 'Precio', ['class' => 'label']) !!}
    {!! Form::text('price', null, ['class' => 'input']) !!}
</p>

<p class="control has-top-padding">
    {!! Form::label('cost', 'Precio de Coste', ['class' => 'label']) !!}
    {!! Form::text('cost', null, ['class' => 'input']) !!}
</p>

<p class="control has-top-padding">
    {!! Form::label('short_description', 'Descripción corta', ['class' => 'label']) !!}
    {!! Form::textarea('short_description', null, ['class' => 'textarea']) !!}
</p>

<p class="control has-top-padding">
    {!! Form::label('description', 'Descripción', ['class' => 'label']) !!}
    {!! Form::textarea('description', null, ['class' => 'textarea']) !!}
</p>

<p class="control has-top-padding">
    {!! Form::checkbox('generate_barcode', 1) !!} Generar Código de Barras
</p>

<p class="control has-top-padding">
    {!! Form::label('ean13', 'Código de barras', ['class' => 'label']) !!}
    {!! Form::text('ean13', null, ['class' => 'input']) !!}
</p>