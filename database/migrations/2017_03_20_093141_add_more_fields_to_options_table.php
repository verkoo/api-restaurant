<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreFieldsToOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('options', function (Blueprint $table) {
            $table->boolean('hide_out_of_stock')->default(0);
            $table->boolean('show_stock_in_photo')->default(0);
            $table->boolean('recount_stock_when_open_cash')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('options', function (Blueprint $table) {
            $table->dropColumn('hide_out_of_stock');
            $table->dropColumn('show_stock_in_photo');
            $table->dropColumn('recount_stock_when_open_cash');
        });
    }
}
