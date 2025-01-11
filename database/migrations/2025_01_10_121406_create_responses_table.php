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
        Schema::create('responses', function (Blueprint $table) {
            $table->id(); // Creates the primary key `id`
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to `users` table
            $table->foreignId('question_id')->constrained()->onDelete('cascade'); // Foreign key to `questions` table
            $table->foreignId('option_id')->constrained()->onDelete('cascade'); // Foreign key to `options` table
            $table->timestamps(); // Creates the `created_at` and `updated_at` columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('responses');
    }
};
