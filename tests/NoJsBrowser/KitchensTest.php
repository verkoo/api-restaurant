<?php

namespace Tests\BrowserNoJs;

use Tests\TestHelpers;
use App\Entities\Kitchen;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class KitchensTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'kitchens';
    }

    protected function getItem()
    {
        return factory(Kitchen::class)->create();
    }
}
