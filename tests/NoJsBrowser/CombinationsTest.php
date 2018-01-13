<?php

namespace Tests\BrowserNoJs;

use Tests\TestHelpers;
use App\Entities\Combination;
use Tests\AcceptanceCrudTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group acceptance
 */
class CombinationsTest extends AcceptanceCrudTestCase
{
    use DatabaseTransactions, TestHelpers;


    protected function getRoute()
    {
        return 'combinations';
    }

    protected function getItem()
    {
        return factory(Combination::class)->create();
    }

    /** @test */
    public function it_shows_the_form_to_add_a_dish_when_visit_edit_page()
    {
        $this->actingAs($this->adminUser())
            ->visit("combinations/{$this->getItem()->id}/edit")
            ->seeIsSelected('dish_id', '')
            ->seeInField('quantity','')
            ->see('Añadir Plato');
    }
}
