<?php

namespace Tests\BrowserNoJs;

use Verkoo\Common\Entities\Box;
use Tests\AcceptanceCrudTestCase;
use Tests\TestHelpers;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class BoxesTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'boxes';
    }

    protected function getItem()
    {
        return factory(Box::class)->create();
    }
}
