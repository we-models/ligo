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
        Schema::table('channels', function (Blueprint $table) {
            //$table->dropUnique(['user1', 'user2', 'profile_user1', 'profile_user2']);
            $table->unique(['user1', 'user2', 'profile_user1', 'profile_user2', 'deleted_at'], 'chanel_user_profile');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
