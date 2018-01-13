<?php

namespace Tests\Unit;

use Verkoo\Common\Entities\Category;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Tax;
use Tests\TestCase;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CategoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_category_has_products()
    {
        $category = create(Category::class);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $category->products);
    }

    /** @test */
    public function a_category_has_a_tax()
    {
        $tax = create(Tax::class);
        $category = factory(Category::class)->create(['tax_id' => $tax->id]);

        $this->assertInstanceOf(Tax::class, $category->tax);
        $this->assertEquals($tax->id, $category->tax->id);
    }

    /** @test */
    public function the_products_count_from_a_category_matches_with_the_active_products_with_stock_if_enabled_in_settings()
    {
        $category = create(Category::class);

        create(Product::class, ['category_id' => $category->id, 'active' => true, 'stock' => 3]);
        create(Product::class, ['category_id' => $category->id, 'active' => true, 'stock' => 3]);
        create(Product::class, ['category_id' => $category->id, 'active' => false, 'stock' => 3]);
        create(Product::class, ['category_id' => $category->id, 'active' => true, 'stock' => 0]);

        $this->assertEquals(3, $category->products_count);

        create(Options::class, ['hide_out_of_stock' => true]);

        $this->assertEquals(2, $category->products_count);
    }
}
