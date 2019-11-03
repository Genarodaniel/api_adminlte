<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UtensilsSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('UtensilsSchedule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('utensil_id')->unsigned()->foreign('utensil_id')->references('id')->on('api.utensil');
            $table->integer('days_work')->unsigned()->foreign('days_work')->references('day')->on('api.daytoday');
            $table->string('work_start');
            $table->string('work_end');
            $table->float('max_time');
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
