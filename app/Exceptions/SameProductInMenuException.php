<?php

namespace App\Exceptions;


class SameProductInMenuException extends \RuntimeException
{
    protected $message = 'Product is already in the order';
}
