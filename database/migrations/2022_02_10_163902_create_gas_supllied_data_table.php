<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGasSuplliedDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gas_supllied_data', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->float('lat',20,10)->comment('latitude - shirina');
            $table->float('long',20,10)->comment('longtitude - dolgota');
            $table->float('gas')->comment('given pressure of gas in system');
            $table->boolean('relay_state')->comment('trun on or off to give command');
            $table->boolean('restored')->comment('is given date restored');
            $table->dateTime('datetime')->nullable();
            $table->integer('speed')->comment('kmph');
            $table->foreignId('vehicle_id')->constrained('vehicle_data','id');

            $table->timestamps();
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
        Schema::dropIfExists('gas_supllied_data');
    }
}
