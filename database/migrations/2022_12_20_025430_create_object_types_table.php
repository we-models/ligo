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
        Schema::create('object_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('description')->nullable();
            $table->boolean('enable')->default(true);
            $table->enum('type', ['post', 'taxonomy']);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'type', 'deleted_at']);
        });

        Schema::create('object_type_relations', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->enum('type', ['unique', 'multiple'])->default('multiple');
            $table->unsignedBigInteger('object_type');
            $table->foreign('object_type')->references('id')->on('object_types')->onDelete('cascade');
            $table->unsignedBigInteger('relation');
            $table->foreign('relation')->references('id')->on('object_types')->onDelete('cascade');
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
        Schema::dropIfExists('object_types');
        Schema::dropIfExists('object_type_relations');
    }
};
