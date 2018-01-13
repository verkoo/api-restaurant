<?php

namespace App\Filters;

class DocumentFilter
{
    private $filter;
    private $documents;

    private $classes = [
        'billed' => Billed::class,
        'not-billed' => NotBilled::class,
        'fully-cashed' => FullyCashed::class,
        'not-cashed' => NotCashed::class,
        'partially-cashed' => PartiallyCashed::class,
        'cashed' => Cashed::class,
    ];

    function __construct($filter, $documents)
    {
        $this->filter = $filter;
        $this->documents = $documents;
    }

    public function apply()
    {
        if (!$this->filter) return $this->documents;

        $filtered = app($this->classes[$this->filter])->apply($this->documents);

        return $filtered->values();
    }
}