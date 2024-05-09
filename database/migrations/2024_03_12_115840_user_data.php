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

            $table->unsignedBigInteger('document_type')->nullable();
            $table->foreign('document_type')->references('id')->on('object_types')->onDelete('cascade');

            $table->unsignedBigInteger('area')->nullable();
            $table->foreign('area')->references('id')->on('object_types')->onDelete('cascade');

            $table->string('ndocument')->after('document_type')->nullable();
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
            $table->dropColumn('document_type');
            $table->dropColumn('ndocument');
            $table->dropColumn('birthday');
            $table->dropColumn('ncontact');
        });
        Schema::dropIfExists('document_type');
    }
};
