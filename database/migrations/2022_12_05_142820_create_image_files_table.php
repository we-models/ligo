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
        Schema::create('image_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->integer('size')->default(0);
            $table->integer('height')->default(100);
            $table->integer('width')->default(100);

            $table->string('extension');
            $table->string('mimetype');

            $table->unsignedBigInteger('business');
            $table->foreign('business')->references('id')->on('business')->onDelete('cascade');

            $table->unsignedBigInteger('user');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');

            $table->enum('visibility', ['public', 'business', 'private'])->default('public');

            $table->string('thumbnail');
            $table->string('small');
            $table->string('medium');
            $table->string('url');
            $table->string('large');
            $table->string('xlarge');


            $table->string('permalink')->unique();
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
        Schema::dropIfExists('image_files');
    }
};
