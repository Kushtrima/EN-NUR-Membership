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
        Schema::create('membership_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->constrained()->onDelete('cascade'); // Original membership payment
            $table->date('membership_start_date');
            $table->date('membership_end_date');
            $table->integer('days_until_expiry')->default(0);
            $table->json('notifications_sent')->nullable(); // Track sent notifications [30, 15, 7, 1]
            $table->timestamp('last_notification_sent_at')->nullable();
            $table->boolean('is_hidden')->default(false); // Admin can hide from renewal alerts
            $table->boolean('is_expired')->default(false);
            $table->boolean('is_renewed')->default(false);
            $table->foreignId('renewal_payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'membership_end_date']);
            $table->index(['days_until_expiry', 'is_hidden', 'is_expired']);
            $table->index('membership_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_renewals');
    }
};
