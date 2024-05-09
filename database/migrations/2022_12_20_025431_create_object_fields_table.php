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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('object_type')->nullable();
            $table->foreign('object_type')->references('id')->on('object_types')->onDelete('cascade');

            $table->string('name');
            $table->string('slug');
            $table->enum('layout', ['tab', 'field']);

            $table->unsignedBigInteger('type')->nullable();
            $table->foreign('type')->references('id')->on('data_type')->onDelete('cascade');

            $table->json('options')->nullable();
            $table->longText('default')->nullable();

            $table->boolean('enable')->default(true);

        });

        Schema::table('fields', function (Blueprint $table) {
            $table->unsignedBigInteger('tab')->nullable();
            $table->foreign('tab')->references('id')->on('fields')->onDelete('cascade');

            $table->unique(['slug', 'deleted_at']);

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
        Schema::dropIfExists('object_has_fields');
    }
};
