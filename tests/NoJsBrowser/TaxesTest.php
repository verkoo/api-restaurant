<?php

namespace Tests\BrowserNoJs;

use Verkoo\Common\Entities\Tax;
use Tests\TestHelpers;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class TaxesTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'taxes';
    }

    protected function getItem()
    {
        return factory(Tax::class)->create();
    }
}
