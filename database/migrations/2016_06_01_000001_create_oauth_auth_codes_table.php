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
        $tables = config('database.tables');
        Schema::create($tables['OA_AU_CO']['table'], function (Blueprint $table) use ($tables) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger($tables['USERS']['id'])->index();
            $table->uuid('client_id');
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = config('database.tables');
        Schema::dropIfExists($tables['OA_AU_CO']['table']);
    }
};
