<?php

namespace Tests;

use App\Entities\Line;
use App\Entities\Product;

abstract class LineTestCase extends TestCase
{
    protected $route;
    protected $document;

    abstract protected function createDocument();

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
        $this->document = $this->createDocument();
    }

    /** @test */
    public function a_document_has_lines()
    {
        $line = factory(Line::class)->make(['product_name' => 'test product line', 'price' => '12,54']);
        $this->document->lines()->save($line);

        $this->get("{$this->route}/{$this->document->id}/edit")
            ->assertSee('Lineas')
            ->assertSee('test product line')
            ->assertSee('12,54');
    }

    /** @test */
    public function it_validates_form_when_creating_a_line()
    {
        $response = $this->json('POST', "{$this->route}/{$this->document->id}/lines");
        $this->assertValidationErrors($response, [
            'product_name',
            'price',
            'quantity',
        ]);
    }

    /** @test */
    public function a_document_can_add_a_line()
    {
        $product = factory(Product::class)->create();

        $response = $this->post("{$this->route}/{$this->document->id}/lines", [
            'product_id'   => $product->id,
            'product_name' => 'Test Product',
            'price' => '12,50',
            'quantity' => '2',
            'discount' => '1',
        ]);

        $this->assertDatabaseHas('lines', [
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'price' => 1250,
            'quantity' => 2,
            'discount' => 100,
            'lineable_id' => $this->document->id,
            'lineable_type' => get_class($this->document)
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating_a_line()
    {
        $line = factory(Line::class)->make();
        $this->document->lines()->save($line);

        $response = $this->json('PATCH', "{$this->route}/{$this->document->id}/lines/{$line->id}");
        $this->assertValidationErrors($response, [
            'product_name',
            'price',
            'quantity',
        ]);
    }

    /** @test */
    public function a_document_can_update_a_line()
    {
        $product = factory(Product::class)->create();
        $line = factory(Line::class)->make(['product_id' => $product->id]);
        $this->document->lines()->save($line);
        $newProduct = factory(Product::class)->create();

        $response = $this->patch("{$this->route}/{$this->document->id}/lines/{$line->id}", [
            'product_id'   => $newProduct->id,
            'product_name' => 'Test Product',
            'price' => '12,50',
            'quantity' => '2',
            'discount' => '1',
        ]);

        $this->assertDatabaseHas('lines', [
            'product_id' => $newProduct->id,
            'product_name' => 'Test Product',
            'price' => 1250,
            'quantity' => 2,
            'discount' => 100,
            'lineable_id' => $this->document->id,
            'lineable_type' => get_class($this->document)
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function a_document_can_delete_a_line()
    {
        $line = factory(Line::class)->make();
        $this->document->lines()->save($line);

        $this->delete("{$this->route}/{$this->document->id}/lines/{$line->id}");

        $this->assertDatabaseMissing('lines', [
            'id' => $line->id,
            'lineable_id' => $this->document->id,
            'lineable_type' => get_class($this->document)
        ]);
    }
}
