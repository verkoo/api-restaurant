<html>
<style>
    body {
        font-family: "Helvetica Neue", Helvetica, Arial;
        font-size: 14px;
        line-height: 20px;
        font-weight: 400;
        color: #3b3b3b;
        -webkit-font-smoothing: antialiased;
        font-smoothing: antialiased;
    }

    .wrapper {
        margin: 0 auto;
        padding: 40px;
        max-width: 800px;
    }

    .table {
        margin: auto;
        /*padding: 40px;*/
        width: 100%;
        display: table;
    }

    .row {
        /*padding: 8px 0;*/
        font-size: 12px;
        width: 100%;
        background: #f6f6f6;
    }
    .row:nth-of-type(odd) {
        background: #ffffff;
    }
    .row.header {
        font-weight: 900;
        color: #ffffff;
        background: #ea6153;
    }
    .row.green {
        background: #27ae60;
    }
    .row.blue {
        background: #2980b9;
    }
    .cell {
        /*padding: 6px 12px;*/
        display: table-cell;
        border: solid black 1px;
        text-align: center;
    }

    .price {
        width: 100px;
    }
    .product {
        width: 200px;
    }
    .allergen {
        width: 55px;
    }
    .title {
        font-family: "Bookman Old Style", Helvetica, Arial;

        font-size: 30px;
        font-weight: bold;
        padding: 20px;
    }
    .total {
        float: right;
        font-weight: bold;
    }
    div.page:not(:last-of-type)
    {
        page-break-after: always;
        page-break-inside: avoid;
    }
</style>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
@foreach($products as $pages)
    <div class="page">
        <div class="title" style="text-align: center;">
            Alérgenos
        </div>

        <div style="text-align: center; padding-bottom: 40px; font-size: 20px">
            <b>Actualizado a {{ date('d-m-Y') }}</b>
        </div>

        <div class="table">
            <div class="row">
                <div class="cell product"></div>

                @foreach($allergens as $allergen)
                    <div class="cell allergen">
                        <img src="{{ public_path("/img/alergenos/{$allergen->icon}.png") }}" width="50px">
                        <span  style="font-size: 10px">{{ $allergen->name }}</span>
                    </div>
                @endforeach
            </div>
                @foreach($pages as $product)
                    <div class="row">
                        <div class="cell product">{{ substr($product->name,0,30) }}</div>

                        @foreach($allergens as $allergen)
                            <div class="cell allergen">
                                <span style="font-size: 20px">
                                    @if($product->hasAllergen($allergen))
                                        &#9679;
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
        </div>
    </div>
@endforeach
</html>


