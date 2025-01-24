<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['page', 'category']); // Differentiates between page and category
            $table->string('type_id'); // Identifier for the page or category (e.g., 'about_us' or '1')
            $table->timestamps();

            $table->unique(['type', 'type_id']); // Ensure unique combinations of type and type_id
        });

        Schema::create('page_meta_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade'); // Links to the `pages` table
            $table->string('language', 5); // Language code (e.g., 'en', 'de')
            $table->string('title'); // Meta title
            $table->text('description'); // Meta description
            $table->json('meta_keywords'); // Meta keywords stored as JSON
            $table->timestamps();

            $table->unique(['page_id', 'language']); // Ensures meta tags are unique for each page and language
        });


    }

    public function down()
    {

    }
};
