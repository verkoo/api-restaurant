<?php

use Verkoo\Common\Entities\Category;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Tax;
use Tests\TestCase;
use App\Entities\Product;
use App\Entities\Allergen;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductTest extends TestCase
{
    use DatabaseTransactions;


    /** @test */
    public function a_product_belongs_to_a_category()
    {
        $product = create(Product::class);

        $this->assertInstanceOf(Category::class, $product->category);
    }

    /** @test */
    public function a_product_has_a_vat()
    {
        $tax = create(Tax::class, ['percentage' => 10]);
        $category = create(Category::class, ['tax_id' => $tax->id]);
        $product = create(Product::class, ['category_id' => $category->id]);

        $this->assertEquals($product->vat, 10);
    }

    /** @test */
    public function a_product_return_default_vat_if_tax_id_is_null_in_category()
    {
        $tax = create(Tax::class, ['percentage' => 10]);
        create(Options::class, ['tax_id' => $tax->id]);
        $category = factory(Category::class)->create(['tax_id' => null]);
        $product = create(Product::class, ['category_id' => $category->id]);

        $this->assertEquals($product->vat, 10);
    }

    /** @test */
    public function it_stores_integer_price()
    {
        $category = factory(Category::class)->create();
        $product = factory(Product::class)->create(['category_id' => $category->id, 'name' => 'test product', 'price' => '1250']);

        $this->assertEquals('1250,00', $product->price);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'price' => 125000
        ]);
    }

    /** @test */
    public function it_stores_price_with_commas()
    {
        $category = factory(Category::class)->create();
        $product = factory(Product::class)->create(['category_id' => $category->id, 'name' => 'test product', 'price' => '12,50']);

        $this->assertEquals('12,50', $product->price);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'price' => 1250
        ]);
    }

    /** @test */
    public function it_stores_price_with_points()
    {
        $category = factory(Category::class)->create();
        $product = factory(Product::class)->create(['category_id' => $category->id, 'name' => 'test product', 'price' => '2.212']);

        $this->assertEquals('2212,00', $product->price);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'price' => 221200
        ]);
    }

    /** @test */
    public function it_stores_price_with_points_and_commas()
    {
        $category = factory(Category::class)->create();
        $product = factory(Product::class)->create(['category_id' => $category->id, 'name' => 'test product', 'price' => '2.212,50']);
        
        $this->assertEquals('2212,50', $product->price);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'price' => 221250
        ]);
    }

    /** @test */
    public function first_to_digits_of_generated_barcode_matches_with_provider_id()
    {
        $providerId = '28';
        $barcode = Product::generateBarcode($providerId);

        $this->assertEquals('28', substr($barcode,0,2));
    }

    /** @test */
    public function first_to_digits_of_generated_barcode_matches_with_provider_id_and_left_pad_with_0()
    {
        $providerId = '8';
        $barcode = Product::generateBarcode($providerId);

        $this->assertEquals('08', substr($barcode,0,2));
    }

    /** @test */
    public function position_three_and_four_of_generated_barcode_matches_with_category_id()
    {
        $providerId = '28';
        $categoryId = '74';
        $barcode = Product::generateBarcode($providerId, $categoryId);

        $this->assertEquals('74', substr($barcode,2,2));
    }

    /** @test */
    public function position_three_and_four_of_generated_barcode_matches_with_category_id_and_left_pad_with_0()
    {
        $providerId = '28';
        $categoryId = '4';
        $barcode = Product::generateBarcode($providerId, $categoryId);

        $this->assertEquals('04', substr($barcode,2,2));
    }

    /** @test */
    public function barcode_has_13_length()
    {
        $providerId = '28';
        $categoryId = '4';
        $barcode = Product::generateBarcode($providerId, $categoryId);

        $this->assertEquals(13, strlen($barcode));
    }

    /** @test */
    public function barcode_must_be_unique()
    {
        $barcodes = array_map(function ($i) {
            return Product::generateBarcode();
        }, range(1,50));

        $this->assertCount(50, array_unique($barcodes));
    }

    /** @test */
    public function a_product_knows_if_has_allergen()
    {
        $product = create(Product::class);

        $allergen1 = create(Allergen::class);
        $allergen2 = create(Allergen::class);

        $product->allergens()->attach($allergen1->id);

        $this->assertTrue($product->hasAllergen($allergen1));
        $this->assertFalse($product->hasAllergen($allergen2));
    }

    /** @test */
    public function it_get_special_price_for_customer()
    {
        $customer = create(Customer::class);

        $product = create(Product::class, ['price' => 10]);

        $customer->products()->attach($product, ['price' => 500]);

        $this->assertEquals('5,00', $product->getPriceFor($customer->id));
    }

    /** @test */
    public function it_get_general_price_for_customer_that_does_not_have_special_price()
    {
        $customer = create(Customer::class);

        $product = create(Product::class, ['price' => 10]);

        $this->assertEquals('10,00', $product->getPriceFor($customer->id));
    }

    /** @test */
    public function it_get_general_price_for_customer_that_does_not_exists()
    {
        $product = create(Product::class, ['price' => 10]);

        $this->assertEquals('10,00', $product->getPriceFor(999));
    }
}