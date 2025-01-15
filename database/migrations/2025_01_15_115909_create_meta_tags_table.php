<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetaTagsTable extends Migration
{
    public function up()
    {
        Schema::create('meta_tags', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'page' or 'category'
            $table->string('type_id'); // Unique ID for page or category
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['type', 'type_id']); // Ensure uniqueness
        });

        Schema::create('meta_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Unique meta keyword name
            $table->timestamps();
        });

        Schema::create('meta_tag_meta_keyword', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meta_tag_id')->constrained('meta_tags')->onDelete('cascade');
            $table->foreignId('meta_keyword_id')->constrained('meta_keywords')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('meta_tag_meta_keyword');
        Schema::dropIfExists('meta_keywords');
        Schema::dropIfExists('meta_tags');
    }
}

