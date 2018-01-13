<?php

namespace Tests\BrowserNoJs;

use Tests\TestHelpers;
use Tests\AcceptanceCrudTestCase;
use Verkoo\Common\Entities\UnitOfMeasure;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class UnitsOfMeasureTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'units-of-measure';
    }

    protected function getItem()
    {
        return factory(UnitOfMeasure::class)->create();
    }
}
