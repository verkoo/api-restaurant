<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSerieToDeliveryNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->unsignedInteger('serie')->default(1);
            $table->string('number');
            $table->integer('cashed_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn('serie');
            $table->dropColumn('number');
            $table->dropColumn('cashed_amount');
        });
    }
}