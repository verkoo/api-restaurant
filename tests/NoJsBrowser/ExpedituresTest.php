<?php

namespace Tests\BrowserNoJs;

use Verkoo\Common\Entities\Expediture;
use Tests\TestHelpers;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class ExpedituresTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'expeditures';
    }

    protected function getItem()
    {
        return factory(Expediture::class)->create();
    }
}
