<?php

namespace Tests\BrowserNoJs;

use Verkoo\Common\Entities\Customer;
use Tests\TestHelpers;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class CustomersTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'customers';
    }

    protected function getItem()
    {
        return factory(Customer::class)->create();
    }
}
