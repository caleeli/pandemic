<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id');
            $table->foreignId('city_id');
            $table->integer('infection')->default(0);
            $table->json('artifacts')->default('{}');
            $table->foreign(['game_id'])
                ->references('id')
                ->on('games')
                ->onDelete('cascade');
            $table->foreign(['city_id'])
                ->references('id')
                ->on('cities')
                ->onDelete('cascade');
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
        Schema::dropIfExists('states');
    }
}
