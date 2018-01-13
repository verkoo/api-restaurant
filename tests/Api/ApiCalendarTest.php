<?php

use Verkoo\Common\Contracts\CalendarInterface;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Services\GoogleCalendar;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiCalendarTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User;
        $this->actingAs($user, 'api');
    }
    
    /** @test */
    public function it_gets_the_events_from_the_calendar()
    {
        $this->disableExceptionHandling();
        $calendarSpy = Mockery::spy(GoogleCalendar::class, ['getEvents' => ['FAKE-ARRAY']]);

        $this->app->instance(CalendarInterface::class, $calendarSpy);

        $this->json('GET', '/api/calendar')
            ->assertStatus(200)
            ->assertJson(['FAKE-ARRAY']);

        $calendarSpy->shouldHaveReceived('getEvents')->once();
    }

    /** @test */
    public function it_stores_a_new_event_in_the_calendar()
    {
        $calendarSpy = Mockery::spy(GoogleCalendar::class);

        $this->app->instance(CalendarInterface::class, $calendarSpy);

        $this->json('POST', '/api/calendar', [
            'start'  => '01/10/2017',
            'end'    => '11/10/2017',
            'title'  => 'SOME TITLE',
        ])
            ->assertStatus(201);

        $calendarSpy->shouldHaveReceived('store')
            ->with([
                'start'  => '01/10/2017',
                'end'    => '11/10/2017',
                'title'  => 'SOME TITLE',
            ])->once();
    }

    /** @test */
    public function start_date_is_required_storing_an_event()
    {
        $response = $this->json('POST', '/api/calendar');

        $this->assertValidationErrors($response, 'start');
    }

    /** @test */
    public function start_date_must_be_in_dd_mm_yy_format_storing_an_event()
    {
        $response = $this->json('POST', '/api/calendar', [
            'start' => 'WRONG DATE'
        ]);

        $this->assertValidationErrors($response, 'start');
    }

    /** @test */
    public function end_date_is_required_storing_an_event()
    {
        $response = $this->json('POST', '/api/calendar');

        $this->assertValidationErrors($response, 'end');
    }

    /** @test */
    public function end_date_must_be_in_dd_mm_yy_format_storing_an_event()
    {
        $response = $this->json('POST', '/api/calendar', [
            'end' => 'WRONG DATE'
        ]);

        $this->assertValidationErrors($response, 'end');
    }

    /** @test */
    public function end_date_must_be_greater_than_start_date_storing_an_event()
    {
        $response = $this->json('POST', '/api/calendar', [
            'start' => '20/10/2017',
            'end' => '19/10/2017',
        ]);

        $this->assertValidationErrors($response, 'end');
    }

    /** @test */
    public function title_is_required_storing_an_event()
    {
        $response = $this->json('POST', '/api/calendar');

        $this->assertValidationErrors($response, 'title');
    }
}