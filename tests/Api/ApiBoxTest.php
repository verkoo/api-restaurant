<?php

use Verkoo\Common\Entities\Box;
use Verkoo\Common\Entities\Category;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Session;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Events\SessionCreated;
use Tests\TestCase;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiBoxTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_boxes()
    {
        create(Box::class, [
            'name'        => 'General',
            'description' => 'Caja General',
        ]);

        $response = $this->get("api/boxes");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name'           => 'General',
            'description'    => 'Caja General',
            'hasOpenSession' => false,
            'lastSession'    => null,
        ]);
    }

    /** @test */
    public function it_creates_a_new_session()
    {
        $this->disableExceptionHandling();
        $box = create(Box::class);

        $response = $this->post("api/boxes", [
            'initial_cash' => '23,50',
            'box_id' => $box->id
        ]);

        $response->assertStatus(200);

        $session = Session::first();

        $this->assertDatabaseHas('sessions', [
            'id'           => $session->id,
            'open'         => 1,
            'initial_cash' => 2350,
            'box_id'       => $box->id
        ]);
    }

    /** @test */
    public function an_event_is_fired_when_a_session_is_created()
    {
        $this->expectsEvents(SessionCreated::class);
        $box = create(Box::class);

        $this->post("api/boxes", [
            'initial_cash' => '23,50',
            'box_id' => $box->id
        ])->assertStatus(200);
    }

    /** @test */
    public function it_creates_a_new_session_and_reset_stock_if_enabled_in_settings()
    {
        create(Options::class, ['recount_stock_when_open_cash' => true]);

        $box = create(Box::class);
        $category = create(Category::class, ['recount_stock' => true]);
        $product = create(Product::class, ['category_id' => $category->id, 'stock' => 2]);
        $otherProduct = create(Product::class, ['stock' => 3]);

        $this->post("api/boxes", [
            'initial_cash' => '23,50',
            'box_id' => $box->id
        ])->assertStatus(200);

        $this->assertEquals(0, $product->fresh()->stock);
        $this->assertEquals(3, $otherProduct->fresh()->stock);
    }

    /** @test */
    public function it_cant_create_a_session_if_there_is_one_open_with_the_same_box()
    {
        $box = create(Box::class);
        create(Session::class, ['box_id' => $box->id]);

        $response = $this->post("api/boxes", [
            'initial_cash' => '23,50',
            'box_id'       => $box->id
        ]);

        $response->assertJsonFragment([
            'success' => false,
            'error'   => 'No puede abrir más de una sesión por caja'
        ]);
    }

    /** @test */
    public function it_closes_a_session()
    {
        $box = create(Box::class);
        create(Session::class, ['initial_cash' => 1, 'box_id' => $box->id]);

        $this->put("api/boxes/{$box->id}", [
            'final_cash' => '23,50'
        ])->assertStatus(200);

        $session = Session::first();

        $this->assertDatabaseHas('sessions', [
            'id'           => $session->id,
            'open'         => 0,
            'initial_cash' => 100,
            'final_cash'   => 2350,
            'box_id'       => $box->id
        ]);
    }
}