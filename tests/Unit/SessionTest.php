<?php

namespace Tests\Unit;

use Mockery;
use Tests\TestCase;
use App\Listeners\ResetStock;
use Verkoo\Common\Events\SessionCreated;

class SessionTest extends TestCase
{
    /** @test */
    public function it_handles_the_reset_stock_listener_when_session_created_event_is_fired()
    {
        $listener = Mockery::spy(ResetStock::class);
        app()->instance(ResetStock::class, $listener);

        event(new SessionCreated());

        $listener->shouldHaveReceived('handle')->once();
    }
}
