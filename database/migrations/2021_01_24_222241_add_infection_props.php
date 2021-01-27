<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInfectionProps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->integer('infectividad')->default(20);
            $table->integer('resistencia')->default(60);
            $table->integer('transmision')->default(50);
            $table->string('type')->default('demo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'infectividad', 'resistencia', 'transmision',
                'type',
                'propiedades',
            ]);
        });
    }
}
