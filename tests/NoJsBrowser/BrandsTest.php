<?php

namespace Tests\BrowserNoJs;

use Verkoo\Common\Entities\Brand;
use Tests\TestHelpers;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class BrandsTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'brands';
    }

    protected function getItem()
    {
        return factory(Brand::class)->create();
    }
}
