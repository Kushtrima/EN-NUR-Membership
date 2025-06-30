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
            // Split name into first_name and last_name
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            
            // Personal information
            $table->date('date_of_birth')->nullable()->after('last_name');
            
            // Address information
            $table->string('address')->nullable()->after('date_of_birth');
            $table->string('postal_code', 10)->nullable()->after('address');
            $table->string('city')->nullable()->after('postal_code');
            
            // Marital status
            $table->enum('marital_status', ['married', 'single'])->nullable()->after('city');
            
            // Contact information
            $table->string('phone_number')->nullable()->after('marital_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name', 
                'date_of_birth',
                'address',
                'postal_code',
                'city',
                'marital_status',
                'phone_number'
            ]);
        });
    }
};
