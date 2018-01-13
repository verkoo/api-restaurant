<?php

namespace Tests\Feature;

use App\Entities\Product;
use Verkoo\Common\Entities\Category;
use Verkoo\Common\Entities\Options;
use Tests\TestCase;
use SGH\PdfBox\PdfBox;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReportProductsWithStockTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_fills_the_pdf_with_the_product_with_stock_and_returns_it_as_attachment()
    {
        create(Options::class, ['recount_stock_when_open_cash' => true, 'hide_out_of_stock' => true]);

        $category = create(Category::class, ['recount_stock' => 1]);

        create(Product::class,['name' => 'Alcachofas', 'priority' => 1, 'price' => '10.20', 'stock' => 20, 'category_id' => $category->id]);
        create(Product::class,['name' => 'Natillas', 'priority' => 1, 'price' => '1.40', 'stock' => 10, 'category_id' => $category->id]);
        create(Product::class,['name' => 'Sin prioridad', 'priority' => 0, 'price' => '1.40', 'stock' => 10, 'category_id' => $category->id]);
        create(Product::class,['name' => 'Este NO', 'price' => '10.20', 'stock' => 0, 'category_id' => $category->id]);
        create(Product::class,['name' => 'Este TAMPOCO', 'price' => '10.20', 'stock' => 10]);

        $response = $this->get("/reports/products");

        $response->assertStatus(200);

        $this->assertEquals('application/pdf', $response->headers->get('content_type'));
        $this->assertEquals('inline; filename="products_report.pdf"', $response->headers->get('content-disposition'));

        $converter = new PdfBox;
        $converter->setPathToPdfBox(base_path() . '/pdfbox-app-2.0.5.jar');
        $pdfText = $converter->textFromPdfStream($response->getContent());

        $this->assertContains('Alcachofas', $pdfText);
        $this->assertContains('10,20 €', $pdfText);

        $this->assertContains('Natillas', $pdfText);
        $this->assertContains('1,40 €', $pdfText);

        $this->assertNotContains('Este NO', $pdfText);
        $this->assertNotContains('Sin Prioridad', $pdfText);
        $this->assertNotContains('Este TAMPOCO', $pdfText);
    }
}
