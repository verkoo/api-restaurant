<?php

use Tests\TestCase;
use App\Entities\Product;
use App\Entities\Allergen;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiProductAllergensTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new \Verkoo\Common\Entities\User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_adds_an_allergen_to_a_product()
    {
        $product = factory(Product::class)->create();
        $allergen = factory(Allergen::class)->create();

        $response = $this->post("api/products/{$product->id}/allergens",[
            'allergen_id' => $allergen->id
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('allergen_product', [
            'product_id' => $product->id,
            'allergen_id' => $allergen->id,
        ]);
    }

    /** @test */
    public function an_allergen_can_not_be_added_if_already_exists_in_a_product()
    {
        $product = factory(Product::class)->create();
        $allergen = factory(Allergen::class)->create();

        $product->allergens()->attach($allergen->id);

        $this->assertCount(1,$product->allergens);

        $this->post("api/products/{$product->id}/allergens",[
            'allergen_id' => $allergen->id
        ]);

        $this->assertCount(1,$product->allergens);
    }

    /** @test */
    public function it_deletes_an_allergen_from_a_product()
    {
        $product = factory(Product::class)->create();
        $allergen = factory(Allergen::class)->create();

        $product->allergens()->attach($allergen->id);

        $response = $this->delete("api/products/{$product->id}/allergens/{$allergen->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('allergen_product', [
            'product_id' => $product->id,
            'allergen_id' => $allergen->id,
        ]);
    }
}