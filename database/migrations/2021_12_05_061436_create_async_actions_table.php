<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsyncActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('async_actions', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->integer('command_int');
            $table->boolean('completed')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicle_data','id');
            $table->index('vehicle_id');
            $table->text('comment')->nullable();
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
        Schema::dropIfExists('async_actions');
    }
}
