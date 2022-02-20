<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnVehicleDataTexosmotr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vehicle_data', function (Blueprint $table) {
            $table->date('texosmotr_valid_till')->nullable();
            $table->date('strxovka_valid_till')->nullable();
            $table->date('tonirovka_valid_till')->nullable();
            $table->date('doverenost_valid_till')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('vehicle_data', function (Blueprint $table) {
            $table->dropColumn('texosmotr_valid_till');
            $table->dropColumn('strxovka_valid_till');
            $table->dropColumn('tonirovka_valid_till');
            $table->dropColumn('doverenost_valid_till');
        });
    }
}
