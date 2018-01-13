<?php

namespace Tests\BrowserNoJs;

use Tests\TestHelpers;
use App\Entities\Table;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class TablesTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'tables';
    }

    protected function getItem()
    {
        return factory(Table::class)->create();
    }
}
