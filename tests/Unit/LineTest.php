<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Product;
use App\Entities\Allergen;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LineTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_line_may_have_children()
    {
        $line = create(Line::class);

        create(Line::class, ['parent' => $line->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $line->children);
    }

    /** @test */
    public function a_line_knows_if_it_has_children()
    {
        $line = create(Line::class);

        $this->assertFalse($line->hasChildren);

        create(Line::class, ['parent' => $line->id]);

        $this->assertTrue($line->fresh()->hasChildren);
    }

    /** @test */
    public function child_lines_are_deleted_when_parent_line_is_deleted()
    {
        $line = create(Line::class);

        $childA = create(Line::class, [ 'parent' => $line->id ]);
        $childB = create(Line::class, [ 'parent' => $line->id ]);
        $anotherLine = create(Line::class);

        $line->delete();

        tap(Line::all()->pluck('id'), function ($lines) use ($anotherLine, $childA, $childB) {
            $this->assertTrue($lines->contains($anotherLine->id));
            $this->assertFalse($lines->contains($childA->id));
            $this->assertFalse($lines->contains($childB->id));
        });
    }

    /** @test */
    public function a_line_can_have_quantity_with_two_decimals_with_dots()
    {
        create(Line::class, [
            'quantity' => 1.23,
            'product_name' => 'TEST'
        ]);

        $this->assertDatabaseHas('lines', [
            'product_name' => 'TEST',
            'quantity' => 1.23
        ]);
    }

    /** @test */
    public function a_line_can_have_quantity_with_three_decimals_with_dots()
    {
        create(Line::class, [
            'quantity' => 1.231,
            'product_name' => 'TEST'
        ]);

        $this->assertDatabaseHas('lines', [
            'product_name' => 'TEST',
            'quantity' => 1.231
        ]);
    }

    /** @test */
    public function a_line_can_have_quantity_with_two_decimals_with_commas()
    {
        create(Line::class, [
            'quantity' => '1,23',
            'product_name' => 'TEST'
        ]);

        $this->assertDatabaseHas('lines', [
            'product_name' => 'TEST',
            'quantity' => 1.23
        ]);
    }

    /** @test */
    public function a_line_can_have_quantity_with_trhee_decimals_with_commas()
    {
        create(Line::class, [
            'quantity' => '1,231',
            'product_name' => 'TEST'
        ]);

        $this->assertDatabaseHas('lines', [
            'product_name' => 'TEST',
            'quantity' => 1.231
        ]);
    }

    /** @test */
    public function a_line_returns_quantity_with_decimals()
    {
        create(Line::class, [
            'quantity' => '1,23',
            'product_name' => 'TEST'
        ]);

        $line = Line::first();

        $this->assertEquals( 1.23, $line->quantity);
    }
}
