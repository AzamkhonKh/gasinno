<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnVehicleData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vehicle_data', function (Blueprint $table) {
            $table->text('qr_text')->unique()->nullable()->comment("with this text you can assign car to owner");
            $table->softDeletes();
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
            $table->dropColumn('qr_text');
            $table->dropSoftDeletes();
        });
    }
}
