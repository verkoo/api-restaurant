<?php

use Tests\TestCase;
use Tests\TestHelpers;
use Verkoo\Common\Entities\Options;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiSettingsTest extends TestCase
{
    use DatabaseTransactions, TestHelpers;
    
    /** @test */
    public function it_gets_the_settings()
    {
        $this->disableExceptionHandling();
        $options = create(Options::class);
        $this->actingAs($this->adminUser(), "api");

        $response = $this->get('/api/settings');
        $response->assertStatus(200)
            ->assertJSON($options->toArray());
    }

    /** @test */
    public function it_updates_the_open_drawer_when_cash_option()
    {
        create(Options::class, ['open_drawer_when_cash' => 1]);

        $this->actingAs($this->adminUser(), "api");

        $response = $this->patch('/api/settings',[
            'open_drawer_when_cash' => 0
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('options', [
            'open_drawer_when_cash' => 0
        ]);
    }

    /** @test */
        public function it_updates_the_print_ticket_when_cash_option()
    {
        create(Options::class, ['print_ticket_when_cash' => 1]);

        $this->actingAs($this->adminUser(), "api");

        $response = $this->patch('/api/settings',[
            'print_ticket_when_cash' => 0
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('options', [
            'print_ticket_when_cash' => 0
        ]);
    }
}
