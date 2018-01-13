<?php
namespace Tests;


abstract class AcceptanceCrudTestCase extends BrowserKitTestCase
{
    protected $displayProperty = 'name';

    abstract protected function getRoute();
    abstract protected function getItem();

    /** @test */
    public function it_shows_the_create_form_when_click_link_in_index_page()
    {
        $this->actingAs($this->adminUser())
            ->visit($this->getRoute())
            ->click('new_link')
            ->seePageIs("{$this->getRoute()}/create");
    }

    /** @test */
    public function it_shows_the_update_form_when_click_link_in_item_row()
    {
        $item = $this->getItem();

        $this->actingAs($this->adminUser())
            ->visit($this->getRoute())
            ->click('edit_link')
            ->seePageIs("{$this->getRoute()}/{$item->id}/edit");
    }

    /** @test */
    public function it_returns_back_when_click_in_return_link_in_create_form()
    {
        $this->actingAs($this->adminUser())
            ->visit("{$this->getRoute()}/create")
            ->click('back_link')
            ->seePageIs($this->getRoute());
    }

    /** @test */
    public function it_returns_back_when_click_in_return_link_in_edit_form()
    {
        $item = $this->getItem();

        $this->actingAs($this->adminUser())
            ->visit("{$this->getRoute()}/{$item->id}/edit")
            ->click('back_link')
            ->seePageIs($this->getRoute());
    }

    /** @test */
    public function it_deletes_a_record_and_returns_back_when_click_in_delete_button_in_edit_form()
    {
        $item = $this->getItem();

        $this->actingAs($this->adminUser())
            ->visit("{$this->getRoute()}/{$item->id}/edit")
            ->press('delete_button')
            ->seePageIs($this->getRoute())
            ->dontSeeInElement('#results', $item->{$this->displayProperty});
    }
}