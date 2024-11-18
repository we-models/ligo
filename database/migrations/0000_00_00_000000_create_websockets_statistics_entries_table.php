<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWebSocketsStatisticsEntriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $tables = config('database.tables');
        Schema::create( $tables['WE_ST_EN']['table'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('app_id');
            $table->integer('peak_connection_count');
            $table->integer('websocket_message_count');
            $table->integer('api_message_count');
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = config('database.tables');
        Schema::dropIfExists($tables['WE_ST_EN']['table']);
    }
}
