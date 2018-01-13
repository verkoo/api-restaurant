<?php

namespace Tests\Feature;

use Tests\TestCase;
use SGH\PdfBox\PdfBox;
use App\Entities\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReportOrdersTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function the_from_and_to_dates_must_be_valid()
    {
        $this->post("/reports/orders", [
            'date_from' => '',
            'date_to' => '',
        ])->assertSessionHasErrors(['date_from', 'date_to']);

        $this->post("/reports/orders", [
            'date_from' => '32/12/2017',
            'date_to' => 'INVALID',
        ])->assertSessionHasErrors(['date_from', 'date_to']);
    }

    /** @test */
    public function it_fills_and_returns_the_pdf()
    {
        $this->disableExceptionHandling();
        create(Order::class,['date' => '02/01/2017', 'serie' => 1]);
        create(Order::class,['date' => '01/02/2017', 'serie' => 1]);
        create(Order::class,['date' => '01/05/2017', 'serie' => 1]);

        $response = $this->post("/reports/orders", [
            'date_from' => '01/01/2017',
            'date_to' => '02/02/2017',
        ]);

        $response->assertStatus(200);

        $this->assertEquals('application/pdf', $response->headers->get('content_type'));
        $this->assertEquals('attachment; filename="orders_report.pdf"', $response->headers->get('content-disposition'));

        $converter = new PdfBox;
        $converter->setPathToPdfBox(base_path() . '/pdfbox-app-2.0.5.jar');
        $pdfText = $converter->textFromPdfStream($response->getContent());

        $this->assertContains('02/01/2017', $pdfText);
        $this->assertContains(date('y') . '0001', $pdfText);

        $this->assertContains('01/02/2017', $pdfText);
        $this->assertContains(date('y') . '0002', $pdfText);

        $this->assertNotContains('01/05/2017', $pdfText);
        $this->assertNotContains(date('y') . '0050', $pdfText);
    }
}
