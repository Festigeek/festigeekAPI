<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->softDeletes();
        });

        Schema::table('orders', function ($table) {
            $table->softDeletes();
        });

        Schema::table('products', function ($table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
