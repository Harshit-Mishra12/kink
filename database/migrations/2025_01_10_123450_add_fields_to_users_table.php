<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Make existing fields nullable
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('language')->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->string('orientation')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('age_category')->nullable()->change();

            // Add role enum with default value 'ANONYMOUSUSER'
            $table->enum('role', ['ADMIN', 'ANONYMOUSUSER'])->default('ANONYMOUSUSER');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the role column if we rollback the migration
            $table->dropColumn('role');
        });
    }
}
