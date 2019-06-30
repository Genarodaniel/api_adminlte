<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCondominiumMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('condominiums', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('address_street');
            $table->integer('address_number');
            $table->string('address_city');
            $table->string('address_complement');
            $table->string('address_state');
            $table->string('address_country');
            $table->string('address_state_abbr');
            $table->integer('manager_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('condominiums', function($table) {
            $table->foreign('manager_id')->references('id')->on('api.user_apps');
        });
     
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('condominiums');
    }
}
