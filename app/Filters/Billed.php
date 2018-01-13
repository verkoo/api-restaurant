<?php

namespace App\Filters;


class Billed
{
    public function apply($documents)
    {
        return $documents->filter(function ($document) {
            return !! $document->invoiceNumber;
        });
    }
}