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
        Schema::table('objects', function (Blueprint $table) {
            $table->dropForeign(['owner']);
            $table->dropColumn('owner');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->unsignedBigInteger('owner')->nullable();
            $table->foreign('owner')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
