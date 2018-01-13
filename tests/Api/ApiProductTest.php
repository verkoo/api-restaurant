<?php

use App\Entities\Extra;
use Verkoo\Common\Entities\Category;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\Options;
use Tests\TestCase;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiProductTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new \Verkoo\Common\Entities\User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_lists_all_the_active_products()
    {
        $product = create(Product::class);

        $response = $this->get('api/products');
        $response->assertStatus(200)
        ->assertJsonFragment([
            'product_id'   => $product->id,
            'product_name' => $product->name,
        ]);
    }

    /** @test */
    public function it_get_the_products_with_extras_if_any()
    {
        $product = create(Product::class);
        $extra = create(Extra::class, [
            'name' => 'KETCHUP'
        ]);
        $product->extras()->attach($extra->id);

        $response = $this->get('api/products');
        $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'KETCHUP'
        ]);
    }

    /** @test */
    public function it_does_not_list_the_inactive_products()
    {
        factory(Product::class)->times(2)->create();
        factory(Product::class)->create(['active' => false]);

        $response = $this->get('api/products')->decodeResponseJson();
        $this->assertCount(2,$response['data']);
    }

    /** @test */
    public function it_hides_out_of_stock_products_if_it_is_activated_in_settings()
    {
        $this->disableExceptionHandling();
        factory(Options::class)->create(['hide_out_of_stock' => 1]);
        $category = factory(Category::class)->create();

        factory(Product::class)->times(2)->create(['stock' => 2, 'category_id' => $category->id]);
        factory(Product::class)->create(['stock' => 0, 'category_id' => $category->id]);

        $response = $this->get('api/products')->decodeResponseJson();
        $this->assertCount(2,$response['data']);

        $response = $this->get("api/categories/{$category->id}/products")->decodeResponseJson();
        $this->assertCount(2,$response['data']);
    }

    /** @test */
    public function it_shows_out_of_stock_products_if_it_is_not_activated_in_settings()
    {
        factory(Options::class)->create(['hide_out_of_stock' => 0]);
        $category = factory(Category::class)->create();

        factory(Product::class)->times(2)->create(['stock' => 2, 'category_id' => $category->id]);
        factory(Product::class)->create(['stock' => 0, 'category_id' => $category->id]);

        $response = $this->get('api/products')->decodeResponseJson();
        $this->assertCount(3,$response['data']);

        $response = $this->get("api/categories/{$category->id}/products")->decodeResponseJson();
        $this->assertCount(3,$response['data']);
    }

    /** @test */
    public function it_lists_the_products_from_a_given_category()
    {
        $category = create(Category::class);
        factory(Product::class)->times(2)->create();
        $targetProduct = make(Product::class);
        $category->products()->save($targetProduct);

        $response = $this->get("api/categories/{$category->id}/products");

            $response->assertStatus(200)
            ->assertJsonFragment($targetProduct->toArray());
    }

    /** @test */
    public function it_lists_the_products_from_a_given_category_with_extras_if_any()
    {
        $category = create(Category::class);
        factory(Product::class)->times(2)->create();
        $targetProduct = create(Product::class, [
            'category_id' => $category->id
        ]);
        $extra = create(Extra::class, [
            'name' => 'KETCHUP'
        ]);
        $targetProduct->extras()->attach($extra->id);

        $this->get("api/categories/{$category->id}/products")
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'KETCHUP'
            ]);
    }

    /** @test */
    public function it_lists_the_products_from_a_given_category_with_pagination()
    {
        $category = factory(Category::class)->create();
        $products = factory(Product::class)->times(10)->make();
        $category->products()->saveMany($products);

        $content = $this->get("api/categories/{$category->id}/products?limit=5")->decodeResponseJson();

        $this->assertCount(5,$content['data']);
        $this->assertEquals($content['paginator'], [
            'total_count'  => 10,
            'total_pages'  => 2,
            'prev_page'    => false,
            'current_page' => 1,
            'next_page'    => 2,
            'limit'        => 5
        ]);
    }

    /** @test */
    public function it_lists_all_the_products_with_pagination()
    {
        factory(Product::class)->times(10)->create();

        $content = $this->get("api/products?limit=5")->decodeResponseJson();

        $this->assertCount(5,$content['data']);
        $this->assertEquals($content['paginator'], [
            'total_count'  => 10,
            'total_pages'  => 2,
            'prev_page'    => false,
            'current_page' => 1,
            'next_page'    => 2,
            'limit'        => 5
        ]);
    }

    /** @test */
        public function it_lists_all_the_products_with_pagination_from_settings()
    {
        create(Options::class, ['pagination' => 5]);

        factory(Product::class)->times(10)->create();

        $content = $this->get("api/products")->decodeResponseJson();

        $this->assertCount(5,$content['data']);
        $this->assertEquals($content['paginator'], [
            'total_count'  => 10,
            'total_pages'  => 2,
            'prev_page'    => false,
            'current_page' => 1,
            'next_page'    => 2,
            'limit'        => 5
        ]);
    }

    /** @test */
        public function it_lists_the_products_from_a_given_category_with_pagination_from_settings()
    {
        create(Options::class, ['pagination' => 5]);

        $category = factory(Category::class)->create();
        $products = factory(Product::class)->times(10)->make();
        $category->products()->saveMany($products);

        $content = $this->get("api/categories/{$category->id}/products")->decodeResponseJson();

        $this->assertCount(5,$content['data']);
        $this->assertEquals($content['paginator'], [
            'total_count'  => 10,
            'total_pages'  => 2,
            'prev_page'    => false,
            'current_page' => 1,
            'next_page'    => 2,
            'limit'        => 5
        ]);
    }

    /** @test */
    public function it_lists_the_products_from_a_parent_category_with_children()
    {
        $parent = factory(Category::class)->create();
        $parent_products = factory(Product::class)->times(4)->make();
        $parent->products()->saveMany($parent_products);

        $child = factory(Category::class)->create(['parent' =>$parent->id]);
        $child_products = factory(Product::class)->times(5)->make();
        $child->products()->saveMany($child_products);

        $other = factory(Category::class)->create();
        $other_products = factory(Product::class)->times(5)->make();
        $other->products()->saveMany($other_products);

        $content = $this->get("api/categories/{$parent->id}/products")->decodeResponseJson();

        $this->assertCount(9,$content['data']);
    }

    /** @test */
    public function it_lists_the_products_from_a_parent_category_with_two_levels_of_children()
    {
        $parent = factory(Category::class)->create();
        $parent_products = factory(Product::class)->times(2)->make();
        $parent->products()->saveMany($parent_products);

        $child = factory(Category::class)->create(['parent' =>$parent->id]);
        $child_products = factory(Product::class)->times(3)->make();
        $child->products()->saveMany($child_products);

        $child2 = factory(Category::class)->create(['parent' =>$child->id]);
        $child2_products = factory(Product::class)->times(2)->make();
        $child2->products()->saveMany($child2_products);

        $other = factory(Category::class)->create();
        $other_products = factory(Product::class)->times(5)->make();
        $other->products()->saveMany($other_products);

        $content = $this->get("api/categories/{$parent->id}/products")->decodeResponseJson();

        $this->assertCount(7,$content['data']);
    }

    /** @test */
    public function it_updates_the_stock_for_the_given_products()
    {
        $product1 = create(Product::class, ['stock' => 0, 'initial_stock' => 0]);
        $product2 = create(Product::class, ['stock' => 0, 'initial_stock' => 0]);
        $product3 = create(Product::class, ['stock' => 3, 'initial_stock' => 3]);

        $this->post('/api/products', [
            'products' => [
                0 => [
                    'id' => $product1->id,
                    'stock' => 1,
                    'initial_stock' => 1,
                ],
                1 => [
                    'id' => $product2->id,
                    'stock' => 2,
                    'initial_stock' => 2,
                ]
            ]
        ]);

        $this->assertEquals(1, $product1->fresh()->stock);
        $this->assertEquals(1, $product1->fresh()->initial_stock);
        $this->assertEquals(2, $product2->fresh()->stock);
        $this->assertEquals(2, $product2->fresh()->initial_stock);
        $this->assertEquals(3, $product3->fresh()->stock);
        $this->assertEquals(3, $product3->fresh()->initial_stock);
    }

    /** @test */
    public function stock_is_required_when_updating_stocks()
    {
        $product1 = create(Product::class, ['stock' => 0]);

        $response = $this->post('/api/products', [
            'products' => [
                0 => [
                    'id' => $product1->id,
                ],
            ]
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function stock_must_be_valid_when_updating_stocks()
    {
        $product1 = create(Product::class, ['stock' => 0]);

        $response = $this->post('/api/products', [
            'products' => [
                0 => [
                    'id' => $product1->id,
                    'stock' => 'NOT VALID',
                ],
            ]
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function it_stores_the_priority_according_to_the_position_in_the_petition_of_update_stock_and_reset_the_rest_of_priorities()
    {
        $product1 = create(Product::class, ['stock' => 0]);
        $product2 = create(Product::class, ['stock' => 0]);
        $product3 = create(Product::class, ['priority' => 3]);

        $this->post('/api/products', [
            'products' => [
                0 => [
                    'id' => $product1->id,
                    'stock' => 1,
                ],
                1 => [
                    'id' => $product2->id,
                    'stock' => 2,
                ]
            ]
        ]);

        $this->assertEquals(1, $product1->fresh()->priority);
        $this->assertEquals(2, $product2->fresh()->priority);
        $this->assertEquals(0, $product3->fresh()->priority);
    }

    /** @test */
    public function it_get_the_product_with_special_price_for_customer()
    {
        $customer = create(Customer::class);

        $product = create(Product::class, ['price' => 100, 'cost' => '0,50']);

        $customer->products()->attach($product, ['price' => 500]);

        $response = $this->json('GET', "/api/products/{$product->id}/customers/{$customer->id}");

        $response->assertJsonFragment([
            'product_id' => $product->id,
            'price' => '5,00',
            'cost' => '0,50',
        ]);
    }
}
