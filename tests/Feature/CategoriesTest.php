<?php

namespace Tests\Feature;

use Tests\TestCase;
use Verkoo\Common\Entities\Tax;
use Verkoo\Common\Entities\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CategoriesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_categories()
    {
        factory(Category::class)->create(['name' => 'Category 1']);

        $this->get('categories')
            ->assertSee('Category 1');
    }
    /** @test */
    public function it_creates_a_new_category()
    {
        $parent = factory(Category::class)->create();
        $tax = create(Tax::class);

        $response = $this->post("categories", [
            'name' => 'Category 1',
            'recount_stock' => 1,
            'parent' => $parent->id,
            'tax_id' => $tax->id,
        ]);

        $this->assertDatabaseHas('categories', [
            'name'   => 'Category 1',
            'recount_stock' => 1,
            'parent' => $parent->id,
            'tax_id' => $tax->id,
        ]);

        $response->assertRedirect('categories');
    }

    /** @test */
    public function it_validates_form_when_creating_a_category()
    {
        $response = $this->json('POST', "categories");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_updates_a_category()
    {
        $category = create(Category::class, ['recount_stock' => 1]);
        $parent = create(Category::class);
        $tax = create(Tax::class);



        $response = $this->json("PATCH", "categories/{$category->id}", [
            'name'   => 'Edit Name',
            'parent' => $parent->id,
            'tax_id' => $tax->id,
        ]);

        $this->assertDatabaseHas('categories', [
            'name'   => 'Edit Name',
            'recount_stock'   => 0,
            'id'     => $category->id,
            'parent' => $parent->id,
            'tax_id' => $tax->id,
        ]);

        $response->assertRedirect('categories');
    }

    /** @test */
    public function it_validates_form_when_updating_a_category()
    {
        $category = factory(Category::class)->create();

        $response = $this->json('PATCH', "categories/{$category->id}");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_category()
    {
        $category = factory(Category::class)->create();

        $response = $this->json('DELETE', "categories/{$category->id}");
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);

        $response->assertRedirect('categories');
    }
}
