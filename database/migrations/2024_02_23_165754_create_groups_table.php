<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('icons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->unsignedBigInteger('icon');
            $table->foreign('icon')->references('id')->on('icons')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('model_has_group',  function (Blueprint $table){
            $table->id();

            $table->unsignedBigInteger('group');
            $table->foreign('group')->references('id')->on('groups');

            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->unique(['group', 'model_type', 'model_id']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = config('database.tables');
        Schema::dropIfExists($tables['MO_HA_GROUP']['table']);
        Schema::dropIfExists($tables['GROUPS']['table']);
    }
};
