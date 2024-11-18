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
        Schema::table('permissions', function (Blueprint $table){
            $table->string('identifier')->after('name')->default('');
            $table->string('detail')->nullable();
            $table->boolean('show_in_menu')->default(true);
            $table->softDeletes();
        });

        Schema::table('roles', function (Blueprint $table){
            $table->longText('description')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('show_in_menu');
            $table->dropColumn('detail');
            $table->dropColumn('identifier');
            $table->dropSoftDeletes();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropSoftDeletes();
        });
    }
};
