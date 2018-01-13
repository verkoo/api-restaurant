<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Entities\Product;
use App\Entities\Kitchen;
use Verkoo\Common\Entities\Brand;
use Verkoo\Common\Entities\Category;
use Verkoo\Common\Entities\Supplier;
use Verkoo\Common\Entities\UnitOfMeasure;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_products()
    {
        factory(Product::class)->create(['name' => 'Product 1']);

        $response = $this->get('products');
        $response->assertSee('Product 1');
    }

    /** @test */
    public function it_creates_a_new_product()
    {
        $unitOfMeasure = create(UnitOfMeasure::class);
        $category = create(Category::class);
        $supplier = create(Supplier::class);
        $brand = create(Brand::class);
        $kitchen = factory(Kitchen::class)->create();

        $response = $this->post("products", [
            'name' => 'Wine',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'brand_id' => $brand->id,
            'kitchen_id' => $kitchen->id,
            'unit_of_measure_id' => $unitOfMeasure->id,
            'stock_control' => '1',
            'stock' => '2',
            'price' => '23,50',
            'cost'  => '12,50',
            'short_description' => 'short description of the product',
            'description' => 'full description of the product',
            'ean13' => '1234567',
        ]);

        $this->assertDatabaseHas('products', [
            'name'              => 'Wine',
            'category_id'       => $category->id,
            'supplier_id'       => $supplier->id,
            'brand_id'          => $brand->id,
            'kitchen_id'        => $kitchen->id,
            'unit_of_measure_id' => $unitOfMeasure->id,
            'stock_control'     => 1,
            'stock'             => 2,
            'price'             => 2350,
            'cost'              => 1250,
            'short_description' => 'short description of the product',
            'description'       => 'full description of the product',
            'ean13'             => '1234567'
        ]);

        $response->assertRedirect('products');
    }

    /** @test */
    public function it_validates_form_when_creating_a_product()
    {
        $response = $this->json('POST', "products");
        $this->assertValidationErrors($response, [
            'name',
            'category_id',
        ]);
    }

    /** @test */
    public function it_updates_a_product()
    {
        $product = create(Product::class);
        $category = create(Category::class);
        $supplier = create(Supplier::class);
        $brand = create(Brand::class);
        $kitchen = factory(Kitchen::class)->create();
        $unitOfMeasure = create(UnitOfMeasure::class);

        $response = $this->patch("products/{$product->id}", [
            'name' => 'Wine',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'brand_id' => $brand->id,
            'kitchen_id' => $kitchen->id,
            'unit_of_measure_id' => $unitOfMeasure->id,
            'stock_control' => '1',
            'stock' => '2',
            'price' => '23,50',
            'cost'  => '12,50',
            'short_description' => 'short description of the product',
            'description' => 'full description of the product',
            'ean13' => '1234567',
        ]);

        $this->assertDatabaseHas('products', [
            'name'              => 'Wine',
            'category_id'       => $category->id,
            'supplier_id'       => $supplier->id,
            'brand_id'          => $brand->id,
            'kitchen_id'        => $kitchen->id,
            'unit_of_measure_id' => $unitOfMeasure->id,
            'stock_control'     => 1,
            'stock'             => 2,
            'price'             => 2350,
            'cost'              => 1250,
            'short_description' => 'short description of the product',
            'description'       => 'full description of the product',
            'ean13'             => '1234567',
            'id'                => $product->id
        ]);

        $response->assertRedirect('products');
    }

    /** @test */
    public function it_validates_form_when_updating_a_product()
    {
        $product = factory(Product::class)->create();

        $response = $this->json('PATCH', "products/{$product->id}");
        $this->assertValidationErrors($response, [
            'name',
            'category_id',
        ]);
    }

    /** @test */
    public function it_deletes_a_product()
    {
        $product = factory(Product::class)->create();

        $this->json('DELETE', "products/{$product->id}");
        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    /** @test */
    public function it_generates_barcode_if_checked_when_creating_a_new_product()
    {
        $category = factory(Category::class)->create();

        $response = $this->post("products", [
            'name' => 'Wine',
            'category_id'       => $category->id,
            'generate_barcode' => 1,
        ]);

        $response->assertRedirect('products');
        $this->assertNotNull(Product::first()->ean13);
    }

    /** @test */
    public function it_generates_barcode_if_checked_when_updating_a_product()
    {
        $product = create(Product::class, ['ean13' => null]);
        $category = factory(Category::class)->create();

        $response = $this->patch("products/{$product->id}", [
            'name' => 'Wine',
            'category_id' => $category->id,
            'generate_barcode' => 1,
        ]);

        $response->assertRedirect('products');
        $this->assertNotNull(Product::first()->ean13);
    }

    /** @test */
    public function the_barcode_must_be_unique_when_creating_a_product()
    {
        $this->actingAs($this->adminUser());
        create(Product::class, ['ean13' => '1234']);
        $category = create(Category::class);

        $response = $this->json('POST','/products', [
            'name' => 'MANDATORY NAME',
            'category_id' => $category->id,
            'ean13' => '1234',
        ]);

        $this->assertValidationErrors($response,'ean13');
    }

    /** @test */
    public function the_barcode_must_be_unique_when_updating_a_product()
    {
        $this->actingAs($this->adminUser());
        create(Product::class, ['ean13' => '1234']);
        $product = create(Product::class, ['ean13' => '6789']);
        $category = create(Category::class);

        $response = $this->json('PATCH',"/products/{$product->id}", [
            'name' => 'MANDATORY NAME',
            'category_id' => $category->id,
            'ean13' => '1234',
        ]);

        $this->assertValidationErrors($response,'ean13');
    }

    /** @test */
    public function it_allows_editing_product_using_same_barcode()
    {
        $this->disableExceptionHandling();
        $this->actingAs($this->adminUser());
        $product = create(Product::class, ['ean13' => '1234']);
        $category = create(Category::class);

        $this->json('PATCH',"/products/{$product->id}", [
            'name' => 'MANDATORY NAME',
            'category_id' => $category->id,
            'ean13' => '1234',
        ])->assertStatus(302);
    }
}
