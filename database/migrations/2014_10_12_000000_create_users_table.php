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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('language'); // Language preference (e.g., English, German, etc.)
            $table->string('gender'); // Gender (e.g., Male, Female, Non-binary, etc.)
            $table->string('orientation'); // Orientation (e.g., Straight, LGBTQ+, etc.)
            $table->string('country'); // Country
            $table->string('age_category');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
