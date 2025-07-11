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
            // For SQLite, we need to drop and recreate the column
            $table->dropColumn('marital_status');
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Add the column back with all four values
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('marital_status');
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Restore the original enum with only two values
            $table->enum('marital_status', ['married', 'single'])->nullable()->after('city');
        });
    }
};
