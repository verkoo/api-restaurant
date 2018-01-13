<?php

namespace Tests\BrowserNoJs;

use Tests\TestHelpers;
use App\Entities\Menu;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class MenusTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'menus';
    }

    protected function getItem()
    {
        return factory(Menu::class)->create();
    }
}
