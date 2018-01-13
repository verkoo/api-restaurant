<?php

namespace Tests\BrowserNoJs;

use Tests\TestHelpers;
use App\Entities\Extra;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class ExtrasTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'extras';
    }

    protected function getItem()
    {
        return factory(Extra::class)->create();
    }
}
