<?php

namespace App\Filters;


class NotCashed
{
    public function apply($documents)
    {
        return $documents->filter(function ($document) {
            return (!$document->cashed_amount) && $document->hasPendingAmount();
        });
    }
}