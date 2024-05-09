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
        Schema::create('objects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->unsignedBigInteger('object_type');
            $table->foreign('object_type')->references('id')->on('object_types')->onDelete('cascade');
            $table->boolean('visible')->default(true);
        });

        Schema::table('objects', function(Blueprint $table){
            $table->unsignedBigInteger('parent')->nullable();
            $table->foreign('parent')->references('id')->on('objects')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('object_relations', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('object');
            $table->foreign('object')->references('id')->on('objects')->onDelete('cascade');
            $table->unsignedBigInteger('relation');
            $table->foreign('relation')->references('id')->on('objects')->onDelete('cascade');
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
        Schema::dropIfExists('object_relations');
    }
};
