<?php

namespace Tests\Unit;

use Verkoo\Common\Entities\Box;
use Verkoo\Common\Entities\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BoxUserUnitTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_adds_an_user_to_a_box()
    {
        $user = factory(User::class)->create();
        $box  = factory(Box::class)->create();
        $box->addUser($user);

        $this->assertDatabaseHas('box_user', [
            'box_id' => $box->id,
            'user_id' => $user->id
        ]);
    }
}
