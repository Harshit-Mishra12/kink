<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_translations', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->unsignedBigInteger('category_id'); // Foreign key to categories table
            $table->string('language'); // Language code (e.g., 'en', 'de')
            $table->string('name'); // Translated category name
            $table->string('title'); // Translated category title
            $table->text('short_description'); // Translated short description
            $table->longText('content'); // Translated HTML content
            $table->timestamps(); // Created_at, updated_at timestamps

            // Add foreign key constraint
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            // Ensure unique translation for each category-language pair
            $table->unique(['category_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_translations');
    }
}
