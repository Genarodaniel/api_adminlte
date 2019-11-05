<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Reserv extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Reserv', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('day');
            $table->integer('utensil_id')->unsigned()->foreign('utensil_id')->references('id')->on('api.utensil');
            $table->integer('user_id')->unsigned()->foreign('user_id')->references('id')->on('users_app');
            $table->integer('time');
            $table->string('hour_start');
            $table->integer('hour_end');

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
    }
}
