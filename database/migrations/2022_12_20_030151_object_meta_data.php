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
        Schema::create('object_field_value', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('object');
            $table->foreign('object')->references('id')->on('objects')->onDelete('cascade');

            $table->unsignedBigInteger('field');
            $table->foreign('field')->references('id')->on('fields')->onDelete('cascade');

            $table->longText('value')->nullable();
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
        Schema::table('object_field_value', function (Blueprint $table) {
            $table->removeColumn('field');
            $table->removeColumn('object');
        });
        Schema::dropIfExists('object_field_value');
        Schema::dropIfExists('object');
        Schema::dropIfExists('fields');
    }
};
