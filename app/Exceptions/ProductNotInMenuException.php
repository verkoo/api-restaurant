<?php

namespace App\Exceptions;


class ProductNotInMenuException extends \RuntimeException
{
    protected $message = 'Product not in menu';
}
