<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('objects', function(Blueprint $table){
            $table->unsignedBigInteger('owner')->nullable()->after('parent');
            $table->foreign('owner')->references('id')->on('users')->onDelete('cascade');

            $table->integer('wp_id')->after('owner')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
