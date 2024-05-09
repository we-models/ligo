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
        Schema::table('object_type_relations', function(Blueprint $table){
            $table->integer('order')->default(0);
            $table->unsignedBigInteger('tab')->nullable()->after('type');
            $table->foreign('tab')->references('id')->on('fields')->onDelete('cascade');
        });

        Schema::table('fields', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('tab');
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
