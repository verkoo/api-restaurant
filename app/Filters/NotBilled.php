<?php

namespace App\Filters;


class NotBilled
{
    public function apply($documents)
    {
        return $documents->filter(function ($document) {
            return ! $document->invoiceNumber;
        });
    }
}