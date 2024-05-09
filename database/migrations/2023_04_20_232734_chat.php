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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user1');
            $table->unsignedBigInteger('profile_user1');
            $table->foreign('profile_user1')->references('id')->on('objects')->onDelete('cascade');

            $table->unsignedBigInteger('user2');
            $table->unsignedBigInteger('profile_user2');
            $table->foreign('profile_user2')->references('id')->on('objects')->onDelete('cascade');

            $table->unsignedBigInteger('intermediary')->nullable();

            $table->foreign('user1')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user2')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('intermediary')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['user1', 'user2', 'profile_user1', 'profile_user2']);


            $table->string('name');

            $table->unique('name');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('transmitter');
            $table->foreign('transmitter')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('receiver');
            $table->foreign('receiver')->references('id')->on('users')->onDelete('cascade');

            $table->boolean('is_from_intermediary')->default(false);


            $table->unsignedBigInteger('channel');
            $table->foreign('channel')->references('id')->on('channels')->onDelete('cascade');

            $table->boolean('is_last')->default(false);
            $table->longText('message')->default('');
            $table->text('media')->nullable();

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
        //
    }
};
