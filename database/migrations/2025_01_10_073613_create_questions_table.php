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

        Schema::create('questions', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->boolean('is_active')->default(true); // Indicates if the question is active
            $table->timestamps(); // Created_at and updated_at timestamps
        });

        Schema::create('question_translations', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->unsignedBigInteger('question_id'); // Foreign key to questions table
            $table->string('language'); // Language code (e.g., 'en', 'fr', 'es')
            $table->text('text'); // Translated question text
            $table->text('hint')->nullable(); // Optional translated hint or explanation
            $table->timestamps(); // Created_at and updated_at timestamps

            // Add foreign key constraint
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');

            // Ensure unique translation for each question-language pair
            $table->unique(['question_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
};
