<?php

use Tests\TestCase;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiCategoryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_returns_the_main_categories()
    {
        factory(Category::class)->times(2)->create();
        $parent1 = factory(Category::class)->create();
        $parent2 = factory(Category::class)->create();

        factory(Category::class)->create(['parent' => $parent1->id]);
        factory(Category::class)->create(['parent' => $parent2->id]);

        $content = $this->get("api/categories")->decodeResponseJson();

        $this->assertCount(4,$content['data']);

    }

    /** @test */
    public function it_returns_the_main_categories_with_pagination()
    {
        create(Options::class, ['pagination' => 6]);
        factory(Category::class)->times(10)->create();

        $content = $this->get('api/categories')->decodeResponseJson();

        $this->assertCount(6,$content['data']);
        $this->assertEquals($content['paginator'], [
            'total_count'  => 10,
            'total_pages'  => 2,
            'prev_page'    => false,
            'current_page' => 1,
            'next_page'    => 2,
            'limit'        => 6
        ]);
    }

    /** @test */
    public function it_gets_the_child_categories_from_a_given_parent_category()
    {
        factory(Category::class)->times(2)->create();
        $parent = factory(Category::class)->create();

        factory(Category::class)->times(3)->create(['parent' => $parent->id]);

        $content = $this->get("api/categories/{$parent->id}")->decodeResponseJson();

        $this->assertCount(3,$content['data']);
    }

    /** @test */
    public function it_gets_the_child_categories_from_a_given_parent_category_with_pagination()
    {
        create(Options::class, ['pagination' => 6]);

        $parent = factory(Category::class)->create();
        factory(Category::class)->times(10)->create(['parent' => $parent->id]);

        $content = $this->get("api/categories/{$parent->id}")->decodeResponseJson();

        $this->assertCount(6,$content['data']);
        $this->assertEquals($content['paginator'], [
            'total_count'  => 10,
            'total_pages'  => 2,
            'prev_page'    => false,
            'current_page' => 1,
            'next_page'    => 2,
            'limit'        => 6
        ]);
    }


    /** @test */
    public function it_gets_the_categories_with_the_count_of_active_products_from_the_category_and_subcategories()
    {
        $category = factory(Category::class)->create();
        $childCategory = factory(Category::class)->create(['parent' => $category->id]);
        $childChildCategory = factory(Category::class)->create(['parent' => $childCategory->id]);

        factory(\App\Entities\Product::class)->create();
        factory(\App\Entities\Product::class)->create(["category_id" => $category->id]);
        factory(\App\Entities\Product::class)->create(["category_id" => $childCategory->id]);
        factory(\App\Entities\Product::class)->create(["category_id" => $childChildCategory->id, 'active' => false]);


        $content = $this->get("api/categories")->decodeResponseJson();

        $this->assertEquals(2,$content['data'][0]['products_count']);
    }


}
