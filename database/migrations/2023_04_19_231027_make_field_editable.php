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
        Schema::table('fields', function (Blueprint $table) {
            $table->boolean('visible_in_app')->after('enable')->default(true);
            $table->boolean('editable')->after('visible_in_app')->default(true);
        });

        Schema::table('object_type_relations', function (Blueprint $table) {
            $table->boolean('enable')->default(true);
            $table->boolean('visible_in_app')->after('enable')->default(true);
            $table->boolean('editable')->after('visible_in_app')->default(true);
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
