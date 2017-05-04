<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('state')->default(0);
            $table->integer('user_id')->unsigned();
            $table->integer('event_id')->unsigned();
            $table->integer('payment_type_id')->unsigned();
            $table->string('paypal_paymentID', 30)->nullable();
            $table->string('code_lan', 20)->nullable();
            $table->binary('data')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users');
            $table->foreign('event_id')
                ->references('id')->on('events');
            $table->foreign('payment_type_id')
                ->references('id')->on('payment_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('orders');
    }
}
