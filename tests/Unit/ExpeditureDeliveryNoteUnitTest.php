<?php

use Verkoo\Common\Entities\ExpeditureDeliveryNote;
use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExpeditureDeliveryNoteUnitTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function lines_are_deleted_when_expediture_delivery_note_is_deleted()
    {
        $deliveryNote = create(ExpeditureDeliveryNote::class);

        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => ExpeditureDeliveryNote::class,
        ]);

        $this->assertDatabaseHas('lines',[
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => ExpeditureDeliveryNote::class,
        ]);

        $deliveryNote->delete();

        $this->assertDatabaseMissing('lines',[
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => ExpeditureDeliveryNote::class,
        ]);
    }

    /** @test */
    public function products_associated_with_lines_restore_their_stocks_when_delivery_note_is_deleted()
    {
        $deliveryNote = create(ExpeditureDeliveryNote::class);
        $product = create(Product::class, ['stock' => 2]);

        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'product_id'    => $product->id,
            'lineable_type' => \Verkoo\Common\Entities\ExpeditureDeliveryNote::class,
        ]);

        $this->assertEquals(3, $product->fresh()->stock);

        $deliveryNote->delete();

        $this->assertEquals(2, $product->fresh()->stock);
    }
}