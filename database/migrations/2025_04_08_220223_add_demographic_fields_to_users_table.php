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
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't exist before adding them to prevent errors
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country', 100)->nullable();
            }
            
            if (!Schema::hasColumn('users', 'continent')) {
                $table->string('continent', 50)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'birth_date', 'country', 'continent']);
        });
    }
};
