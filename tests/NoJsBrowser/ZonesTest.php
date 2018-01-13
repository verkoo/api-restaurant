<?php

namespace Tests\BrowserNoJs;

use Tests\TestHelpers;
use App\Entities\Zone;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class ZonesTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'zones';
    }

    protected function getItem()
    {
        return factory(Zone::class)->create();
    }
}
