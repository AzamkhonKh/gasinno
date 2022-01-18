<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDriverTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('driver_data', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->constrained('users','id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('driver_data', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }
}
