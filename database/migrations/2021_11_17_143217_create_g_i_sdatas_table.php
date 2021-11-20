<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGISdatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('GISdatas', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->float('lat',20,10)->comment('latitude - shirina');
            $table->float('long',20,10)->comment('longtitude - dolgota');
            $table->float('gas')->comment('given pressure of gas in system');
            $table->boolean('relay_state')->comment('trun on or off to give command');
            $table->dateTime('datetime')->nullable();
            $table->integer('speed')->comment('kmph');
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
        Schema::dropIfExists('g_i_sdatas');
    }
}
