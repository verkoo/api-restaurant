<?php

use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Payment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cash_customer');
            
            $table->foreign('cash_customer')
                ->references('id')
                ->on('customers');

            $table->unsignedInteger('payment_id');

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments');

            $table->string('company_name');
            $table->string('address');
            $table->string('cif');
            $table->string('phone');
            $table->string('default_printer');
            $table->timestamps();
        });

        if(\Config::get('app.env') == 'production') {
            $customer = Customer::create([
                'name'     => 'Cliente contado',
                'dni'      => '',
                'phone'    => '',
                'email'    => '',
                'comments' => ''
            ]);

            $payment = Payment::create(["name" => 'Contado']);

            Options::create([
                'cash_customer' => $customer->id,
                'payment_id' => $payment->id
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
        Schema::drop('options');
    }
}
