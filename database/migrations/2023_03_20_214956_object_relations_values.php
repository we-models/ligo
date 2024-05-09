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
        Schema::table('object_relations' , function(Blueprint $table){
            $table->unsignedBigInteger('relation_object')->nullable()->after('relation');
            $table->foreign('relation_object')->references('id')->on('object_type_relations')->onDelete('cascade');
            $table->unique(['object', 'relation', 'relation_object'], 'unique_values_to_relation_object');
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
