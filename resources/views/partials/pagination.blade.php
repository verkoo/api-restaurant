<nav class="pagination">
    <ul class="pagination-list">

    <!-- Previous Page Link -->
    @if ($paginator->onFirstPage())
            <a class="pagination-previous" title="Página Anterior" disabled>Anterior</a>
    @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-previous">Anterior</a>
    @endif

<!-- Pagination Elements -->
    @foreach ($elements as $element)
    <!-- "Three Dots" Separator -->
        @if (is_string($element))
            <li>
                <span class="pagination-ellipsis">&hellip;</span>
            </li>
        @endif

    <!-- Array Of Links -->
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <a class="pagination-link is-current">{{ $page }}</a>
                @else
                    <li>
                        <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    </li>
                @endif
            @endforeach
        @endif
    @endforeach

<!-- Next Page Link -->
    @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-next">Siguiente</a>
    @else
            <a class="pagination-next" title="Página Siguiente" disabled>Siguiente</a>
    @endif
    </ul>
</nav>