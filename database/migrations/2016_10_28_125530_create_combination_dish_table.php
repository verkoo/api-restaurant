<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCombinationDishTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('combination_dish', function (Blueprint $table) {
            $table->unsignedInteger('combination_id');
            $table->unsignedInteger('dish_id');
            $table->integer('quantity');

            $table->foreign('combination_id')
                ->references('id')
                ->on('combinations')
                ->onDelete('cascade');

            $table->foreign('dish_id')
                ->references('id')
                ->on('dishes')
                ->onDelete('cascade');

            $table->primary(['combination_id', 'dish_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('combination_dish');
    }
}
