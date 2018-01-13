<?php

namespace App\Filters;


class PartiallyCashed
{
    public function apply($documents)
    {
        return $documents->filter(function ($document) {
            return $document->cashed_amount > 0 && $document->hasPendingAmount();
        });
    }
}