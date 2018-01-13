<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuOrderProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_order_product', function (Blueprint $table) {
            $table->unsignedInteger('menu_order_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('dish_id');

            $table->foreign('menu_order_id')
                ->references('id')
                ->on('menu_orders')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('dish_id')
                ->references('id')
                ->on('dishes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_order_product');
    }
}
