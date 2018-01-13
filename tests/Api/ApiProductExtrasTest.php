<?php

use Tests\TestCase;
use App\Entities\Extra;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiProductExtrasTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new \Verkoo\Common\Entities\User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_extras()
    {
        $product = create(Product::class);
        $extra = create(Extra::class);

        $response = $this->get("api/products/{$product->id}/extras");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $extra->id
        ]);
    }

    /** @test */
    public function it_adds_an_extra_to_a_product()
    {
        $this->disableExceptionHandling();
        $product = create(Product::class);
        $extra = create(Extra::class);

        $response = $this->post("api/products/{$product->id}/extras",[
            'extra_id' => $extra->id
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('extra_product', [
            'product_id' => $product->id,
            'extra_id' => $extra->id,
        ]);
    }

    /** @test */
    public function an_extra_can_not_be_added_if_already_exists_in_a_product()
    {
        $product = create(Product::class);
        $extra = create(Extra::class);

        $product->extras()->attach($extra->id);

        $this->assertCount(1,$product->extras);

        $this->post("api/products/{$product->id}/extras",[
            'extra_id' => $extra->id
        ]);

        $this->assertCount(1,$product->extras);
    }

    /** @test */
    public function it_deletes_an_extra_from_a_product()
    {
        $product = create(Product::class);
        $extra = create(Extra::class);

        $product->extras()->attach($extra->id);

        $response = $this->delete("api/products/{$product->id}/extras/{$extra->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('extra_product', [
            'product_id' => $product->id,
            'extra_id' => $extra->id,
        ]);
    }
}