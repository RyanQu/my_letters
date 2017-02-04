<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('records');
        Schema::create('records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('line');
            $table->integer('row');
            $table->char('unit');
            $table->string('color')->default('');
            $table->timestamps();
        });//
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
