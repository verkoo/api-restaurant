<?php

namespace Tests\Feature;

use Verkoo\Common\Entities\Customer;
use Tests\TestCase;
use SGH\PdfBox\PdfBox;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReportCashPendingTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function amount_is_required_when_generating_pdf_cash_recipe()
    {
        $response = $this->json('post', '/reports/cash-pending', [
            'items' => [0 => 1],
            'customer' => 1,
        ]);

        $this->assertValidationErrors($response, 'amount');
    }

    /** @test */
    public function items_are_required_when_generating_pdf_cash_recipe()
    {
        $response = $this->json('post', '/reports/cash-pending', [
            'amount' => 10,
            'customer' => 1,
        ]);

        $this->assertValidationErrors($response, 'items');
    }

    /** @test */
    public function customer_is_required_when_generating_pdf_cash_recipe()
    {
        $response = $this->json('post', '/reports/cash-pending', [
            'amount' => 10,
            'items' => [0 => 1]
        ]);

        $this->assertValidationErrors($response, 'customer');
    }

    /** @test */
    public function pending_is_required_when_generating_pdf_cash_recipe()
    {
        $response = $this->json('post', '/reports/cash-pending', [
            'amount' => 10,
            'customer' => 1,
            'items' => [0 => 1]
        ]);

        $this->assertValidationErrors($response, 'pending');
    }

    /** @test */
    public function it_generates_the_cash_pdf_when_hit_the_endpoint()
    {
        $customer = create(Customer::class, [
            'name' => 'John-Doe'
        ]);

        $response = $this->post('/reports/cash-pending', [
            'amount' => 10,
            'customer' => $customer->id,
            'pending' => 1,
            'items' => [
                'full' => [
                    0 => [
                        'number' => date('y') . '0001',
                        'date' => '20/01/2017',
                    ]
                ],
                'partial' => [
                    0 => [
                        'number' => date('y') . '0002',
                        'date' => '28/01/2017',
                    ]
                ],
            ]
        ]);

        $response->assertStatus(200);

        $this->assertEquals('application/pdf', $response->headers->get('content_type'));
        $this->assertEquals('inline; filename="cash_pending_report.pdf"', $response->headers->get('content-disposition'));

        $converter = new PdfBox;
        $converter->setPathToPdfBox(base_path() . '/pdfbox-app-2.0.5.jar');
        $pdfText = $converter->textFromPdfStream($response->getContent());

        $this->assertContains('John-Doe', $pdfText);
        $this->assertContains('20/01/2017', $pdfText);
        $this->assertContains(date('y') . '0001', $pdfText);
        $this->assertContains('28/01/2017', $pdfText);
        $this->assertContains(date('y') . '0002', $pdfText);
    }
}
