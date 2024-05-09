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

        Schema::table('users', function (Blueprint $table) {
            $table->string('lastname')->after('name')->nullable();

            $table->boolean('enable')->after('lastname')->default(true);

            $table->string('ndocument')->after('lastname')->nullable();
            $table->string('birthday')->after('ndocument')->nullable();
            $table->string('ncontact')->after('birthday')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('lastname');
            $table->dropColumn('ndocument');
            $table->dropColumn('birthday');
            $table->dropColumn('ncontact');
        });
    }
};
