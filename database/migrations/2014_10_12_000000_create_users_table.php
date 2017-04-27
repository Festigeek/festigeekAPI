<?php

use Illuminate\Support\Facades\Schema;
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
            $table->date('birthdate');

            $table->enum('gender', ['M', 'F'])->nullable();
            $table->string('lastname', 255)->nullable();
            $table->string('firstname', 255)->nullable();
            $table->integer('country_id')->unsigned()->nullable();
            $table->string('street', 100)->nullable();
            $table->string('street2', 100)->nullable();
            $table->string('npa', 25)->nullable();
            $table->string('city', 200)->nullable();

            $table->string('lol_account', 20)->nullable();
            $table->string('steamID64', 20)->nullable();
            $table->string('battleTag', 20)->nullable();

            $table->integer('lang')->nullable();
            $table->boolean('activated')->default(false);
            $table->string('registration_token')->index()->nullable();
            $table->integer('drupal_id')->unsigned()->nullable();
            $table->string('drupal_password', 255)->nullable();

            //$table->rememberToken();
            $table->timestamps();

            $table->foreign('country_id')
                ->references('id')->on('countries');
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
