<?php

namespace App\Filters;


class FullyCashed
{
    public function apply($documents)
    {
        return $documents->filter(function ($document) {
            return ! $document->hasPendingAmount();
        });
    }
}