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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('size')->default(0);

            $table->string('extension');
            $table->string('mimetype');

            $table->unsignedBigInteger('business');
            $table->foreign('business')->references('id')->on('business')->onDelete('cascade');

            $table->unsignedBigInteger('user');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');

            $table->enum('visibility', ['public', 'business', 'private'])->default('public');

            $table->string('url');

            $table->string('permalink')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('model_has_file', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->unsignedBigInteger('file');
            $table->foreign('file')->references('id')->on('files')->onDelete('cascade');

            $table->string('field')->default('files');

            $table->unique(['model_type', 'model_id', 'file', 'field']);

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
        Schema::dropIfExists('model_has_file');
        Schema::dropIfExists('files');
    }
};
