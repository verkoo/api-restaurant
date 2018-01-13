<?php

namespace Tests\Unit;

use Verkoo\Common\Entities\Category;
use Tests\TestCase;
use App\Entities\Product;
use App\Entities\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class InventoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_resets_the_stock_of_the_products_which_recountable_category()
    {
        $category = create(Category::class, ['recount_stock' => 1]);

        $product1 = create(Product::class, ['category_id' => $category->id, 'stock' => 2]);
        $product2 = create(Product::class, ['category_id' => $category->id, 'stock' => 3]);

        $notMatchingProduct = create(Product::class, ['stock' => 5]);

        Inventory::resetStockInRecountableCategories();

        $this->assertEquals(0, $product1->fresh()->stock);
        $this->assertEquals(0, $product2->fresh()->stock);
        $this->assertEquals(5, $notMatchingProduct->fresh()->stock);
    }
}
