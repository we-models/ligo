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
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('message')->default('');
            $table->string('file')->default('');
            $table->integer('Line')->default(0);
            $table->text('trace')->default('');
            $table->timestamps();
        });
        /*
        $e->getMessage();
            $e->getFile();
            $e->getLine();
            $e->getTraceAsString();
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('error_logs');
    }
};
