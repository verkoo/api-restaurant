<?php

namespace Tests\BrowserNoJs;

use Tests\TestHelpers;
use App\Entities\Dish;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class DishesTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'dishes';
    }

    protected function getItem()
    {
        return factory(Dish::class)->create();
    }
}
