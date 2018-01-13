<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function ($table) {
            $table->integer('cashed')->unsigned()->nullable()->default(null)->change();
        });

        Schema::table('orders', function ($table) {
            $table->renameColumn('cashed', 'payment_id');
            $table->foreign('payment_id')->references('id')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function ($table) {
            $table->dropForeign(['payment_id']);
        });

        Schema::table('orders', function ($table) {
            $table->boolean('payment_id')->default(false)->change();
            $table->renameColumn('payment_id', 'cashed');
        });

    }
}
