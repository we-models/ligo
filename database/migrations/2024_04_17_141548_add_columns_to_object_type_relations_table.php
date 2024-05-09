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
        Schema::table('object_type_relations', function (Blueprint $table) {
            $table->enum('type_relationship', ['object', 'user'])->default('object')->after('required')->nullable();
            $table->unsignedBigInteger('relation')->nullable()->change();
        });

        Schema::table('object_relations', function (Blueprint $table) {
            $table->string('model_type')->after('object');
            $table->dropForeign(['relation']);
        });

        Schema::table('object_types', function (Blueprint $table) {
            $table->boolean('show_description')->default(true)->after('access_code');
            $table->boolean('show_image')->default(true)->after('access_code');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('object_type_relations', function (Blueprint $table) {
            $table->dropColumn('type_relationship');
        });

        Schema::table('object_relations', function (Blueprint $table) {
            $table->dropColumn('model_type');
        });

        Schema::table('object_relations', function (Blueprint $table) {
            $table->dropColumn('show_description');
            $table->dropColumn('show_image');
        });
    }
};
