<?php

use Verkoo\Common\Entities\Quote;
use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class QuoteUnitTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function lines_are_deleted_when_quote_is_deleted()
    {
        $quote = create(Quote::class);

        create(Line::class, [
            'lineable_id'   => $quote->id,
            'lineable_type' => Quote::class,
        ]);

        $this->assertDatabaseHas('lines',[
            'lineable_id'   => $quote->id,
            'lineable_type' => Quote::class,
        ]);

        $quote->delete();

        $this->assertDatabaseMissing('lines',[
            'lineable_id'   => $quote->id,
            'lineable_type' => Quote::class,
        ]);
    }

    /** @test */
    public function products_associated_with_lines_restore_their_stocks_when_invoice_is_deleted()
    {
        $quote = create(Quote::class);
        $product = create(Product::class, ['stock' => 2]);

        create(Line::class, [
            'lineable_id'   => $quote->id,
            'product_id'    => $product->id,
            'lineable_type' => Quote::class,
        ]);

        $this->assertEquals(1, $product->fresh()->stock);

        $quote->delete();

        $this->assertEquals(2, $product->fresh()->stock);
    }

    /** @test */
    public function it_stores_discount_in_cents()
    {
        $quote = create(Quote::class, [
            'discount' => '12,50'
        ]);

        $this->assertEquals(12.5, $quote->discount);
        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'discount' => 1250
        ]);
    }

    /** @test */
    public function total_amount_in_cents_is_the_sum_of_the_lines_minus_discount()
    {
        $quote = create(Quote::class, [
            'discount' => '1,20'
        ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $quote->lines()->save($line1);
        $quote->lines()->save($line2);

        $this->assertEquals(840, $quote->total_in_cents);
    }

    /** @test */
    public function subtotalInCents_is_the_sum_of_the_lines_without_discount()
    {
        $quote = create(Quote::class, [ 'discount' => '1,20' ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $quote->lines()->save($line1);
        $quote->lines()->save($line2);

        $this->assertEquals(960, $quote->subtotalInCents);
    }

    /** @test */
    public function subtotal_is_formatted_subtotal()
    {
        $quote = create(Quote::class, [ 'discount' => '1,20' ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $quote->lines()->save($line1);
        $quote->lines()->save($line2);

        $this->assertEquals('9,60', $quote->subtotal);
    }
}