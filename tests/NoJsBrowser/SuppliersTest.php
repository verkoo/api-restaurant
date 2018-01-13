<?php

namespace Tests\BrowserNoJs;

use Tests\TestHelpers;
use Tests\AcceptanceCrudTestCase;
use Verkoo\Common\Entities\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class SuppliersTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'suppliers';
    }

    protected function getItem()
    {
        return factory(Supplier::class)->create();
    }
}
