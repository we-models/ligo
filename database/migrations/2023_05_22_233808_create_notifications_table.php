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

        Schema::table('sliders', function (Blueprint $table) {
            if(!Schema::hasColumn('sliders','role')){
                $table->unsignedBigInteger('role')->nullable();
                $table->foreign('role')->references('id')->on('roles')->onDelete('cascade');
            }
        });

        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('background');
            $table->string('color');
            $table->string('sound')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content');
            $table->string('link')->nullable();
            $table->unsignedBigInteger('type')->nullable();
            $table->foreign('type')->references('id')->on('notification_types')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('notification_receivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification')->nullable();
            $table->foreign('notification')->references('id')->on('notifications')->onDelete('cascade');

            $table->unsignedBigInteger('role')->nullable();
            $table->foreign('role')->references('id')->on('roles')->onDelete('cascade');

            $table->unsignedBigInteger('user')->nullable();
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('receiver')->nullable();
            $table->foreign('receiver')->references('id')->on('notification_receivers')->onDelete('cascade');

            $table->unsignedBigInteger('user')->nullable();
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('object')->nullable();
            $table->foreign('object')->references('id')->on('objects')->onDelete('cascade');

            $table->boolean('read')->default(false);

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
        Schema::dropIfExists('notifications');
    }
};
