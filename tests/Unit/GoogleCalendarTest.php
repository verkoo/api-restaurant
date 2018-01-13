<?php

use Verkoo\Common\Services\GoogleCalendar;
use Tests\TestCase;

class GoogleCalendarTest extends TestCase
{

    /** @test */
    public function it_gets_the_events_with_the_right_format()
    {
        $event = new \Spatie\GoogleCalendar\Event();

        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDate('2017-10-01');

        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDate('2017-10-02');

        $event->googleEvent->setStart($start);
        $event->googleEvent->setEnd($end);
        $event->googleEvent->setSummary('SOME TITLE');

        $events = collect([$event]);

        $calendar = new GoogleCalendar;

        $response = $calendar->getEvents($events);

        $this->assertEquals([
            '2017-10-01' => [
                0 => [
                    'title' => 'SOME TITLE'
                ]
            ]
        ], $response);
    }

    /** @test */
    public function it_gets_the_events_grouped_in_days()
    {
        $eventA = new \Spatie\GoogleCalendar\Event();
        $eventB = new \Spatie\GoogleCalendar\Event();

        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDate('2017-10-01');

        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDate('2017-10-02');

        $eventA->googleEvent->setStart($start);
        $eventA->googleEvent->setEnd($end);
        $eventA->googleEvent->setSummary('TITLE FOR A');

        $eventB->googleEvent->setStart($start);
        $eventB->googleEvent->setEnd($end);
        $eventB->googleEvent->setSummary('TITLE FOR B');

        $events = collect([$eventA, $eventB]);

        $calendar = new GoogleCalendar;

        $response = $calendar->getEvents($events);

        $this->assertEquals([
            '2017-10-01' => [
                0 => [
                    'title' => 'TITLE FOR A'
                ],
                1 => [
                    'title' => 'TITLE FOR B'
                ]
            ]
        ], $response);
    }

    /** @test */
    public function it_gets_two_events_with_different_dates()
    {
        $eventA = new \Spatie\GoogleCalendar\Event();
        $eventB = new \Spatie\GoogleCalendar\Event();

        $startA = new Google_Service_Calendar_EventDateTime();
        $startA->setDate('2017-10-01');

        $endA = new Google_Service_Calendar_EventDateTime();
        $endA->setDate('2017-10-02');

        $startB = new Google_Service_Calendar_EventDateTime();
        $startB->setDate('2017-10-02');

        $endB = new Google_Service_Calendar_EventDateTime();
        $endB->setDate('2017-10-03');

        $eventA->googleEvent->setStart($startA);
        $eventA->googleEvent->setEnd($endA);
        $eventA->googleEvent->setSummary('TITLE FOR A');

        $eventB->googleEvent->setStart($startB);
        $eventB->googleEvent->setEnd($endB);
        $eventB->googleEvent->setSummary('TITLE FOR B');

        $events = collect([$eventA, $eventB]);

        $calendar = new GoogleCalendar;

        $response = $calendar->getEvents($events);

        $this->assertEquals([
            '2017-10-01' => [
                0 => [
                    'title' => 'TITLE FOR A'
                ],
            ],
            '2017-10-02' => [
                0 => [
                    'title' => 'TITLE FOR B'
                ]
            ]
        ], $response);
    }

    /** @test */
    public function it_gets_two_events_if_event_occurs_in_two_days()
    {
        $eventA = new \Spatie\GoogleCalendar\Event();

        $startA = new Google_Service_Calendar_EventDateTime();
        $startA->setDate('2017-10-01');

        $endA = new Google_Service_Calendar_EventDateTime();
        $endA->setDate('2017-10-03');

        $eventA->googleEvent->setStart($startA);
        $eventA->googleEvent->setEnd($endA);
        $eventA->googleEvent->setSummary('TITLE FOR A');

        $events = collect([$eventA]);

        $calendar = new GoogleCalendar;

        $response = $calendar->getEvents($events);

        $this->assertEquals([
            '2017-10-01' => [
                0 => [
                    'title' => 'TITLE FOR A'
                ],
            ],
            '2017-10-02' => [
                0 => [
                    'title' => 'TITLE FOR A'
                ]
            ]
        ], $response);
    }
}