<?php

namespace Tests\BrowserNoJs;

use Verkoo\Common\Entities\Payment;
use Tests\TestHelpers;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class PaymentsTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'payments';
    }

    protected function getItem()
    {
        return factory(Payment::class)->create();
    }
}
