<?php

namespace Tests\BrowserNoJs;

use Verkoo\Common\Entities\ExpeditureType;
use Tests\TestHelpers;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class ExpeditureTypesTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'expediture-types';
    }

    protected function getItem()
    {
        return factory(ExpeditureType::class)->create();
    }
}
