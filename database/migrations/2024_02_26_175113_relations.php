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
        echo "Creating data_type table \n";
        Schema::create('data_type', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        echo "Creating configurations table \n";
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description');
            $table->longText('default')->nullable();
            $table->unsignedBigInteger('type');
            $table->foreign('type')->references('id')->on('data_type')->onDelete('cascade');

            $table->boolean('custom_by_user')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });

        echo "Creating system_configs table \n";
        Schema::create('system_configs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('configuration');
            $table->foreign('configuration')->references('id')->on('configurations')->onDelete('cascade');

            $table->longText('value');

            $table->unsignedBigInteger('user')->nullable();
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
            $table->unique(['configuration', 'user', 'deleted_at']);
        });

        echo "Creating image_files table \n";
        Schema::create('image_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->integer('size')->default(0);
            $table->integer('height')->default(100);
            $table->integer('width')->default(100);

            $table->string('extension');
            $table->string('mimetype');

            $table->unsignedBigInteger('user');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');
            $table->string('url');
            $table->string('permalink')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        echo "Creating model_has_image table \n";
        Schema::create('model_has_image', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->unsignedBigInteger('image');
            $table->foreign('image')->references('id')->on('image_files')->onDelete('cascade');

            $table->string('field')->default('images');

            $table->unique(['model_type', 'model_id', 'image', 'field']);

            $table->timestamps();
        });

        echo "Creating files table \n";
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('size')->default(0);

            $table->string('extension');
            $table->string('mimetype');

            $table->unsignedBigInteger('user');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');

            $table->string('url');

            $table->string('permalink')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        echo "Creating model_has_file table \n";
        Schema::create('model_has_file', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->unsignedBigInteger('file');
            $table->foreign('file')->references('id')->on('files')->onDelete('cascade');

            $table->string('field')->default('files');

            $table->unique(['model_type', 'model_id', 'file', 'field']);

            $table->timestamps();
        });

        echo "Creating user_manipulations table \n";
        Schema::create('user_manipulations', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->enum('type', ['created', 'updated', 'deleted'])->default('created');
            $table->text('details')->nullable();

            $table->unsignedBigInteger('user');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });

        echo "Creating object_types table \n";
        Schema::create('object_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 20);
            $table->string('prefix', 20)->nullable();
            $table->string('description')->nullable();
            $table->boolean('enable')->default(true);
            $table->enum('type', ['post', 'taxonomy'])->default('post');
            $table->boolean('public')->default(true);
            $table->string('access_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'type', 'deleted_at']);
        });

        echo "Creating fields table \n";
        Schema::create('fields', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('object_type')->nullable();
            $table->foreign('object_type')->references('id')->on('object_types')->onDelete('cascade');

            $table->string('name');
            $table->string('slug');
            $table->enum('layout', ['tab', 'field'])->default('field');

            $table->unsignedBigInteger('type')->nullable();
            $table->foreign('type')->references('id')->on('data_type')->onDelete('cascade');

            $table->json('options')->nullable();
            $table->longText('accept')->nullable();
            $table->longText('default')->nullable();

            $table->boolean('enable')->default(true);
            $table->longText('description')->nullable();

            $table->boolean('editable')->default(true);

        });

        echo "Creating fields table \n";
        Schema::table('fields', function (Blueprint $table) {
            $table->unsignedBigInteger('tab')->nullable();
            $table->foreign('tab')->references('id')->on('fields')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->boolean('required')->default(false);
            $table->unique(['slug', 'deleted_at']);

            $table->timestamps();
            $table->softDeletes();
        });

        echo "Creating object_type_relations table \n";
        Schema::create('object_type_relations', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->enum('type', ['unique', 'multiple'])->default('multiple');
            $table->unsignedBigInteger('object_type');
            $table->foreign('object_type')->references('id')->on('object_types')->onDelete('cascade');
            $table->unsignedBigInteger('relation');
            $table->foreign('relation')->references('id')->on('object_types')->onDelete('cascade');
            $table->enum('filling_method', ['creation', 'selection', 'own_selection', 'all'])->default('selection');
            $table->integer('order')->default(0);
            $table->unsignedBigInteger('tab')->nullable();
            $table->foreign('tab')->references('id')->on('fields')->onDelete('cascade');
            $table->boolean('enable')->default(true);
            $table->longText('description')->nullable();
            $table->boolean('editable')->default(true);
            $table->boolean('required')->default(false);
            $table->unique(['slug', 'deleted_at']);
            $table->timestamps();
            $table->softDeletes();
        });

        echo "Creating objects table \n";
        Schema::create('objects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_id');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->unsignedBigInteger('object_type');
            $table->foreign('object_type')->references('id')->on('object_types')->onDelete('cascade');
            $table->boolean('visible')->default(true);
        });

        echo "Creating objects table \n";
        Schema::table('objects', function(Blueprint $table){
            $table->unsignedBigInteger('parent')->nullable();
            $table->foreign('parent')->references('id')->on('objects')->onDelete('cascade');

            $table->unsignedBigInteger('owner')->nullable();
            $table->foreign('owner')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        echo "Creating object_relations table \n";
        Schema::create('object_relations', function(Blueprint $table){
            $table->id();

            $table->unsignedBigInteger('object');
            $table->foreign('object')->references('id')->on('objects')->onDelete('cascade');

            $table->unsignedBigInteger('relation');
            $table->foreign('relation')->references('id')->on('objects')->onDelete('cascade');

            $table->unsignedBigInteger('relation_object')->nullable();
            $table->foreign('relation_object')->references('id')->on('object_type_relations')->onDelete('cascade');

            $table->unique(['object', 'relation', 'relation_object'], 'unique_values_to_relation_object');
            $table->timestamps();
        });

        echo "Creating object_field_value table \n";
        Schema::create('object_field_value', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('object');
            $table->foreign('object')->references('id')->on('objects')->onDelete('cascade');

            $table->unsignedBigInteger('field');
            $table->foreign('field')->references('id')->on('fields')->onDelete('cascade');

            $table->longText('value')->nullable();
            $table->timestamps();
        });

        echo "Creating error_logs table \n";
        Schema::table('error_logs', function (Blueprint $table) {
            $table->longText('message')->change();
        });

        echo "Creating rating_types table \n";
        Schema::create('rating_types', function(Blueprint $table){
            $table->id();
            $table->string('name');

            $table->unsignedBigInteger('object_type')->nullable();
            $table->foreign('object_type')->references('id')->on('object_types')->onDelete('cascade');


            $table->timestamps();
            $table->softDeletes();
        });

        echo "Creating comments table \n";
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('object');
            $table->foreign('object')->references('id')->on('objects')->onDelete('cascade');

            $table->unsignedBigInteger('user')->nullable();
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        echo "Creating comment_ratings table \n";
        Schema::create('comment_ratings', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('rating_type');
            $table->foreign('rating_type')->references('id')->on('rating_types')->onDelete('cascade');

            $table->unsignedBigInteger('comment');
            $table->foreign('comment')->references('id')->on('comments')->onDelete('cascade');

            $table->integer('rating')->default(0);

            $table->unique(['rating_type', 'comment']);
            $table->timestamps();

        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
