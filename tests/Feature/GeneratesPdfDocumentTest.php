<?php

namespace Tests\Feature;

use App\Entities\Order;
use Verkoo\Common\Entities\Address;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\DeliveryNote;
use Verkoo\Common\Entities\Invoice;
use Verkoo\Common\Entities\Quote;
use Tests\TestCase;
use SGH\PdfBox\PdfBox;
use App\Entities\Line;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GeneratesPdfDocumentTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_generates_pdf_document_from_order()
    {
        $this->disableExceptionHandling();
        $customer = create(Customer::class, ['name' => 'JOHN', 'dni' => 'TESTDNI']);
        create(Address::class, [
            'customer_id'   => $customer->id,
            'city'        => 'MURCIA',
            'postcode'    => '30170',
            'province'    => '30',
            'address'     => 'TESTSTREET',
            'default'     => true
        ]);
        $order = create(Order::class, ['customer_id' => $customer->id]);
        create(Line::class, [
            'lineable_id'   => $order->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class,
            'product_name'  => 'PRODUCT-A',
        ]);
        create(Line::class, [
            'lineable_id'   => $order->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class,
            'product_name'  => 'PRODUCT-B',
        ]);
        $response = $this->get("documents/orders/{$order->id}");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content_type'));
        $this->assertEquals("inline; filename=\"orders_{$order->id}.pdf\"", $response->headers->get('content-disposition'));
        $converter = new PdfBox;
        $converter->setPathToPdfBox(base_path() . '/pdfbox-app-2.0.5.jar');
        tap($converter->textFromPdfStream($response->getContent()), function ($pdfText) {
            $this->assertContains('JOHN', $pdfText);
            $this->assertContains('TESTDNI', $pdfText);
            $this->assertContains('MURCIA', $pdfText);
            $this->assertContains('30170', $pdfText);
            $this->assertContains('TESTSTREET', $pdfText);
            $this->assertContains('Pedido', $pdfText);
            $this->assertContains('PRODUCT-A', $pdfText);
            $this->assertContains('PRODUCT-B', $pdfText);
        });
    }

    /** @test */
    public function it_generates_pdf_document_from_quote()
    {
        $customer = create(Customer::class, ['name' => 'JOHN', 'dni' => 'TESTDNI']);
        create(Address::class, [
            'customer_id'   => $customer->id,
            'city'        => 'MURCIA',
            'postcode'    => '30170',
            'province'    => '30',
            'address'     => 'TESTSTREET',
            'default'     => true
        ]);
        $quote = create(Quote::class, ['customer_id' => $customer->id]);
        create(Line::class, [
            'lineable_id'   => $quote->id,
            'lineable_type' => Quote::class,
            'product_name'  => 'PRODUCT-A',
        ]);
        create(Line::class, [
            'lineable_id'   => $quote->id,
            'lineable_type' => Quote::class,
            'product_name'  => 'PRODUCT-B',
        ]);
        $response = $this->get("documents/quotes/{$quote->id}");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content_type'));
        $this->assertEquals("inline; filename=\"quotes_{$quote->id}.pdf\"", $response->headers->get('content-disposition'));
        $converter = new PdfBox;
        $converter->setPathToPdfBox(base_path() . '/pdfbox-app-2.0.5.jar');
        tap($converter->textFromPdfStream($response->getContent()), function ($pdfText) {
            $this->assertContains('JOHN', $pdfText);
            $this->assertContains('TESTDNI', $pdfText);
            $this->assertContains('MURCIA', $pdfText);
            $this->assertContains('30170', $pdfText);
            $this->assertContains('TESTSTREET', $pdfText);
            $this->assertContains('Presupuesto', $pdfText);
            $this->assertContains('PRODUCT-A', $pdfText);
            $this->assertContains('PRODUCT-B', $pdfText);
        });
    }

    /** @test */
    public function it_generates_pdf_document_from_delivery_note()
    {
        $customer = create(Customer::class, ['name' => 'JOHN', 'dni' => 'TESTDNI']);
        create(Address::class, [
            'customer_id'   => $customer->id,
            'city'        => 'MURCIA',
            'postcode'    => '30170',
            'province'    => '30',
            'address'     => 'TESTSTREET',
            'default'     => true
        ]);
        $deliveryNote = create(DeliveryNote::class, ['customer_id' => $customer->id]);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'product_name'  => 'PRODUCT-A',
        ]);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'product_name'  => 'PRODUCT-B',
        ]);
        $response = $this->get("documents/delivery-notes/{$deliveryNote->id}");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content_type'));
        $this->assertEquals("inline; filename=\"delivery-notes_{$deliveryNote->id}.pdf\"", $response->headers->get('content-disposition'));
        $converter = new PdfBox;
        $converter->setPathToPdfBox(base_path() . '/pdfbox-app-2.0.5.jar');
        tap($converter->textFromPdfStream($response->getContent()), function ($pdfText) {
            $this->assertContains('JOHN', $pdfText);
            $this->assertContains('TESTDNI', $pdfText);
            $this->assertContains('MURCIA', $pdfText);
            $this->assertContains('30170', $pdfText);
            $this->assertContains('TESTSTREET', $pdfText);
            $this->assertContains('Albarán', $pdfText);
            $this->assertContains('PRODUCT-A', $pdfText);
            $this->assertContains('PRODUCT-B', $pdfText);
        });
    }

    /** @test */
    public function it_generates_pdf_document_from_invoice()
    {
        $customer = create(Customer::class, ['name' => 'JOHN', 'dni' => 'TESTDNI']);
        create(Address::class, [
            'customer_id'   => $customer->id,
            'city'        => 'MURCIA',
            'postcode'    => '30170',
            'province'    => '30',
            'address'     => 'TESTSTREET',
            'default'     => true
        ]);
        $invoice = create(Invoice::class, ['customer_id' => $customer->id]);
        create(Line::class, [
            'lineable_id'   => $invoice->id,
            'lineable_type' => Invoice::class,
            'product_name'  => 'PRODUCT-A',
        ]);
        create(Line::class, [
            'lineable_id'   => $invoice->id,
            'lineable_type' => Invoice::class,
            'product_name'  => 'PRODUCT-B',
        ]);
        $response = $this->get("documents/invoices/{$invoice->id}");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content_type'));
        $this->assertEquals("inline; filename=\"invoices_{$invoice->id}.pdf\"", $response->headers->get('content-disposition'));
        $converter = new PdfBox;
        $converter->setPathToPdfBox(base_path() . '/pdfbox-app-2.0.5.jar');
        tap($converter->textFromPdfStream($response->getContent()), function ($pdfText) {
            $this->assertContains('JOHN', $pdfText);
            $this->assertContains('TESTDNI', $pdfText);
            $this->assertContains('MURCIA', $pdfText);
            $this->assertContains('30170', $pdfText);
            $this->assertContains('TESTSTREET', $pdfText);
            $this->assertContains('Factura', $pdfText);
            $this->assertContains('PRODUCT-A', $pdfText);
            $this->assertContains('PRODUCT-B', $pdfText);
        });
    }
}
