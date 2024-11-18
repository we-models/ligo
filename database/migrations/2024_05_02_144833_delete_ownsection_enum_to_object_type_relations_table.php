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
            $table->enum('filling_method', ['creation', 'selection', 'all'])->default('selection')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('object_type_relations', function (Blueprint $table) {
            $table->enum('filling_method', ['creation', 'selection', 'own_selection', 'all'])->default('selection')->change();
        });
    }
};
