<?php

use Verkoo\Common\Entities\Tax;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBreakDownTaxesInTicketAndTaxIdToOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(\Config::get('app.env') == 'production') {
            $tax = Tax::create(['name' => 'General', 'percentage' => 21]);
            Schema::table('options', function (Blueprint $table) use ($tax) {
                $table->boolean('break_down_taxes_in_ticket')->default(0);
                $table->unsignedInteger('tax_id')->default($tax->id);
                $table->foreign('tax_id')->references('id')->on('taxes');
            });
        } else {
            Schema::table('options', function (Blueprint $table) {
                $table->boolean('break_down_taxes_in_ticket')->default(0);
                $table->unsignedInteger('tax_id');
                $table->foreign('tax_id')->references('id')->on('taxes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('options', function (Blueprint $table) {
            $table->dropColumn('break_down_taxes_in_ticket');
            $table->dropColumn('tax_id');
            $table->dropForeign(['tax_id']);
        });
    }
}
