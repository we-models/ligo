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
        if (!Schema::hasTable('data_type')) {
            Schema::create('data_type', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
                $table->softDeletes();
            });
        }


        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description');
            $table->longText('default')->nullable();
            $table->unsignedBigInteger('type');
            $table->foreign('type')->references('id')->on('data_type')->onDelete('cascade');

            $table->boolean('custom_by_user')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('system_configs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('configuration');
            $table->foreign('configuration')->references('id')->on('configurations')->onDelete('cascade');

            $table->longText('value');

            $table->unsignedBigInteger('business');
            $table->foreign('business')->references('id')->on('business')->onDelete('cascade');

            $table->unsignedBigInteger('user')->nullable();
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
            $table->unique(['configuration', 'business', 'user', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_configs');
        Schema::dropIfExists('configurations');
    }
};
