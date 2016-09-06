<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('username', 100);
            $table->string('password', 64)->nullable();
            $table->string('lastname', 255)->nullable();
            $table->string('firstname', 255)->nullable();
            $table->date('birthdate');
            $table->integer('lang')->nullable();
            $table->boolean('activated')->default(false);
            $table->string('registration_token')->index()->nullable();

            $table->integer('drupal_id')->unsigned()->nullable();
            $table->string('drupal_password', 255)->nullable();

            //$table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
