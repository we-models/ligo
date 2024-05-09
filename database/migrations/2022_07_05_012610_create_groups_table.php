<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();

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

        Schema::table('permissions', function (Blueprint $table){
            $table->string('identifier')->after('name')->default('');
            $table->string('detail')->nullable();
            $table->boolean('show_in_menu')->default(true);
            $table->softDeletes();
        });

        Schema::table('roles', function (Blueprint $table){

            $table->boolean('public')->default(false);
            $table->string('icon')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_admin')->default(false);
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
        Schema::dropIfExists('model_has_group');
        Schema::dropIfExists('groups');
    }
}
