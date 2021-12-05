<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users','id');
            $table->double('balloon_volume',20,10);
            $table->string('car_number',10);
            $table->string('car_model',255)->nullable();
            $table->string('token',255)->nullable();
            $table->boolean('verified')->comment('does this car should work with this owner')->default(false);
            $table->timestamps();
            $table->index('token');
        });

        Schema::table('GISdatas', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->constrained('vehicle_data','id');
        });
        Schema::table('ip_data', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->constrained('vehicle_data','id');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_data');
    }
}
