<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKitchenIdAndOrderedFieldsToMenuOrderProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('menu_order_product', function (Blueprint $table) {
            $table->integer('kitchen_id')->nullable();
            $table->boolean('ordered')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menu_order_product', function (Blueprint $table) {
            $table->dropColumn('kitchen_id');
            $table->dropColumn('ordered');
        });
    }
}
