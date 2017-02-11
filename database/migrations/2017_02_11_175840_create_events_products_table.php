<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('events_products', function (Blueprint $table) {
            $table->increments('id');
            $table->float('price', 8, 2);
            $table->integer('product_id')->unsigned();
            $table->integer('event_id')->unsigned();
            $table->timestamps();

            $table->foreign('event_id')
                ->references('id')->on('events');

            $table->foreign('product_id')
                ->references('id')->on('products');
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
        Schema::drop('events_products');
    }
}
