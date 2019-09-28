<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UtensilCond extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('utensilsCond', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('condominium_id')->unsigned()->foreign('condominium_id')->references('id')->on('api.condominiums');
            $table->integer('utensil_id')->unsigned()->foreign('utensil_id')->references('id')->on('api.utensils');
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
