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
        Schema::create('rating_types', function(Blueprint $table){
            $table->id();
            $table->string('name');

            $table->unsignedBigInteger('object_type')->nullable();
            $table->foreign('object_type')->references('id')->on('object_types')->onDelete('cascade');


            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('object');
            $table->foreign('object')->references('id')->on('objects')->onDelete('cascade');

            $table->unsignedBigInteger('user')->nullable();
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('comment_ratings', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('rating_type');
            $table->foreign('rating_type')->references('id')->on('rating_types')->onDelete('cascade');

            $table->unsignedBigInteger('comment');
            $table->foreign('comment')->references('id')->on('comments')->onDelete('cascade');

            $table->integer('rating')->default(0);

            $table->unique(['rating_type', 'comment']);
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
        Schema::dropIfExists('comments');
    }
};
