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
        Schema::create($tables['USERS']['table'], function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('email');
            $table->string('fcm_token')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['email', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = config('database.tables');
        Schema::dropIfExists($tables['USERS']['table']);
    }
};
