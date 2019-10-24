<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserCond extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usersCond', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('condominium_id')->unsigned()->foreign('condominium_id')->references('id')->on('api.condominiums');
            $table->integer('user_id')->unsigned()->foreign('user_id')->references('id')->on('api.user_apps');
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
        //
    }
}
