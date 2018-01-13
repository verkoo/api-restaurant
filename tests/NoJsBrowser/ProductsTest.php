<?php

namespace Tests\BrowserNoJs;

use Tests\TestHelpers;
use App\Entities\Product;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class ProductsTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'products';
    }

    protected function getItem()
    {
        return factory(Product::class)->create();
    }
}
