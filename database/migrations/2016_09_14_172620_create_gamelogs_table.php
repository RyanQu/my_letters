<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamelogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('gamellogs');
        Schema::create('gamelogs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('order');
            $table->string('word');
            $table->string('color');
            $table->timestamps();
        });////
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
