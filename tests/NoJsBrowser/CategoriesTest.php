<?php

namespace Tests\BrowserNoJs;

use Verkoo\Common\Entities\Category;
use Tests\TestHelpers;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class CategoriesTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'categories';
    }

    protected function getItem()
    {
        return factory(Category::class)->create();
    }
}
