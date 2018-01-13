<?php


use App\Entities\Order;
use Verkoo\Common\Entities\DeliveryNote;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function it_returns_formatted_price_without_dots_and_commas()
    {
        $result = toFloat('2125');

        $this->assertEquals('2125', $result);
    }

    /** @test */
    public function it_returns_formatted_price_with_dots_in_thousands()
    {
        $result = toFloat('2.125');

        $this->assertEquals('2125', $result);
    }
    
    /** @test */
    public function it_returns_formatted_price_with_dot_in_decimals()
    {
        $result = toFloat('21.25');

        $this->assertEquals('21.25', $result);
    }

    /** @test */
    public function it_returns_formatted_price_with_dot_and_commas()
    {
        $result = toFloat('21.250,23');

        $this->assertEquals('21250.23', $result);
    }

    /** @test */
    public function it_returns_year_with_four_digits_when_passing_two()
    {
        $result = getDateWithFourDigitsYear('21/12/16');

        $this->assertEquals('21/12/2016', $result);
    }

    /** @test */
    public function it_returns_year_with_four_digits_when_passing_more_than_four()
    {
        $result = getDateWithFourDigitsYear('21/12/20166');

        $this->assertEquals('21/12/2016', $result);
    }

    /** @test */
    public function it_returns_associated_model_from_a_given_one_word_route()
    {
        $order = factory(Order::class)->create();
        $result = getAssociatedModel('orders', $order->id);

        $this->assertInstanceOf($order->getMorphClass(), $result);
    }

    /** @test */
    public function it_returns_associated_model_from_a_given_two_words_route()
    {
        $deliveryNote = factory(DeliveryNote::class)->create();
        $result = getAssociatedModel('delivery-notes', $deliveryNote->id);

        $this->assertInstanceOf(DeliveryNote::class, $result);
    }
}