<?php

namespace App\Filters;


class Cashed
{
    public function apply($documents)
    {
        return $documents->filter(function ($document) {
            return $document->cashed_amount > 0;
        });
    }
}