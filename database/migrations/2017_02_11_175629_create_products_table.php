<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->float('price', 8, 2)->default(0);
            $table->integer('quantity_max')->unsigned()->nullable();
            $table->integer('sold')->unsigned()->nullable();
            $table->boolean('need_team')->default(false);
            $table->integer('product_type_id')->unsigned();
            $table->integer('event_id')->unsigned()->nullable();
            $table->binary('data')->nullable();
            $table->timestamps();

            $table->foreign('event_id')
                ->references('id')->on('events');

            $table->foreign('product_type_id')
                ->references('id')->on('product_types');
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
        Schema::drop('products');
    }
}
