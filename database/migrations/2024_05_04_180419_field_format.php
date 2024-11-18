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
        Schema::table('fields', function (Blueprint $table) {
            $table->boolean('show_tab_name')->after('layout')->default(true);
            $table->enum('format', ['collapse', 'section'])->after('layout')->default('collapse');

            $table->integer('width')->after('layout')->default(4);
        });

        Schema::table('object_type_relations', function (Blueprint $table) {
            $table->integer('width')->after('tab')->default(4);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
