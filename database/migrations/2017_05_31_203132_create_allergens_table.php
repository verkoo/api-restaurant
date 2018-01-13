<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllergensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allergens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('icon');
            $table->timestamps();
        });

        $allergens = [
          'Cereales con Glúten' => 'cereales',
          'Crustáceos' => 'crustaceos',
          'Huevos' => 'huevos',
          'Pescado' => 'pescado',
          'Cacahuetes' => 'cacahuetes',
          'Soja' => 'soja',
          'Lácteos' => 'lacteos',
          'Frutos Secos' => 'frutos_secos',
          'Apio' => 'apio',
          'Mostaza' => 'mostaza',
          'Sésamo' => 'sesamo',
          'Sulfitos' => 'sulfitos',
          'Altramuz' => 'altramuz',
          'Moluscos' => 'moluscos',
        ];

        foreach ($allergens as $name => $icon) {
            \App\Entities\Allergen::create([
                'name' => $name,
                'icon' => $icon,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allergens');
    }
}
