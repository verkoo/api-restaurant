<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lines', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lineable_id');
            $table->string('lineable_type');
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedInteger('kitchen_id')->nullable();
            $table->foreign('kitchen_id')->references('id')->on('kitchens');
            $table->unsignedInteger('ordered')->default(0);
            $table->string('product_name');
            $table->integer('quantity');
            $table->integer('discount')->nullable();
            $table->integer('price');
            $table->integer('vat');
            $table->integer('cost');
            $table->string('quote_number')->nullable();
            $table->string('customer_invoice_number')->nullable();
            $table->string('customer_delivery_note_number')->nullable();
            $table->string('supplier_delivery_note_number')->nullable();
            $table->string('order_number')->nullable();
            $table->string('supplier_order_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lines');
    }
}
