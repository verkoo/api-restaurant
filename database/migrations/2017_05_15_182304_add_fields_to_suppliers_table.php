<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('phone');
            $table->string('phone2');
            $table->string('contact');
            $table->string('email');
            $table->string('address');
            $table->string('web');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('phone2');
            $table->dropColumn('contact');
            $table->dropColumn('email');
            $table->dropColumn('address');
            $table->dropColumn('web');
        });
    }
}
