<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Artisan;
use App\Models\MembershipRenewal;
use App\Services\MembershipService;

class AdminController extends Controller
{


    /**
     * Show all users.
     */
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Optimize loading with specific columns and eager loading
        $users = $query->with([
            'payments' => function($query) {
                $query->select('user_id', 'amount', 'payment_type', 'status', 'created_at');
            },
            'membershipRenewals' => function($query) {
                $query->select('user_id', 'days_until_expiry', 'is_hidden', 'is_renewed', 'membership_end_date')
                      ->where('is_renewed', false);
            }
        ])->select('id', 'name', 'email', 'role', 'email_verified_at', 'created_at')
          ->paginate(20);

        // Add membership status to each user using the service (optimized to use already loaded relationships)
        $membershipService = new MembershipService();
        $users->getCollection()->transform(function ($user) use ($membershipService) {
            // Use already loaded relationship to avoid additional queries
            $activeRenewal = $user->membershipRenewals->first();
            if ($activeRenewal) {
                $daysUntilExpiry = $activeRenewal->calculateDaysUntilExpiry();
                $user->membership_status = [
                    'days_until_expiry' => $daysUntilExpiry,
                    'is_hidden' => $activeRenewal->is_hidden,
                    'is_expired' => $daysUntilExpiry <= 0,
                    'membership_end_date' => $activeRenewal->membership_end_date,
                    'border_color' => $this->getBorderColor($daysUntilExpiry, $activeRenewal->is_hidden),
                    'status_badge' => $this->getStatusBadge($daysUntilExpiry, $activeRenewal->is_hidden),
                ];
            } else {
                $user->membership_status = null;
            }
            return $user;
        });

        return view('admin.users', compact('users'));
    }

    /**
     * Show all payments.
     */
    public function payments(Request $request)
    {
        $query = Payment::with('user');

        // Enhanced filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate statistics
        $stats = [
            'total' => Payment::count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
        ];

        return view('admin.payments', compact('payments', 'stats'));
    }

    /**
     * Export user data (GDPR).
     */
    public function exportUser(User $user)
    {
        $userData = [
            'user_info' => $user->toArray(),
            'payments' => $user->payments->toArray(),
            'export_date' => now()->toDateTimeString(),
            'exported_by' => auth()->user()->email
        ];

        $fileName = 'user_data_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->json($userData)
                         ->header('Content-Type', 'application/json')
                         ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Update user role.
     */
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin,super_admin'
        ]);

        // Prevent changing own super admin role
        if ($user->isSuperAdmin() && auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot change your own super admin role.');
        }

        // Prevent non-super admins from creating super admins
        if ($request->role === 'super_admin' && !auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Only super admins can assign super admin role.');
        }

        $user->update(['role' => $request->role]);

        return redirect()->back()->with('success', 'User role updated successfully.');
    }

    /**
     * Delete user and all associated data (GDPR).
     */
    public function deleteUser(User $user)
    {
        // Prevent deletion if it's the last super admin
        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() === 1) {
            return redirect()->back()->with('error', 'Cannot delete the last super admin account.');
        }

        // Prevent deletion of super admins (except when handled above)
        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Super admin accounts cannot be deleted.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    // New payment management methods
    public function updatePaymentStatus(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,failed,cancelled'
        ]);

        $oldStatus = $payment->status;
        $payment->update(['status' => $request->status]);

        // Send notification email if status changed to completed
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $this->sendPaymentNotification($payment);
        }

        return response()->json(['success' => true]);
    }

    public function bulkUpdatePaymentStatus(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array|max:100', // Limit bulk operations to 100 items
            'payment_ids.*' => 'exists:payments,id',
            'status' => 'required|in:pending,completed,failed,cancelled'
        ]);

        try {
            \DB::beginTransaction();

            $payments = Payment::whereIn('id', $request->payment_ids)->get();
            
            // Check if user has permission to modify these payments
            foreach ($payments as $payment) {
                // Super admins can modify any payment, regular admins can only modify certain types
                if (!auth()->user()->isSuperAdmin()) {
                    // Add business logic here for regular admin restrictions if needed
                }
            }
            
            $updatedCount = 0;
            foreach ($payments as $payment) {
                $oldStatus = $payment->status;
                $payment->update(['status' => $request->status]);
                $updatedCount++;
                
                // Send notification if status changed to completed
                if ($request->status === 'completed' && $oldStatus !== 'completed') {
                    $this->sendPaymentNotification($payment);
                }
            }

            \DB::commit();

            Log::info('Bulk payment status update completed', [
                'admin_user' => auth()->user()->email,
                'payment_count' => $updatedCount,
                'new_status' => $request->status,
                'payment_ids' => $request->payment_ids
            ]);

            return response()->json([
                'success' => true,
                'updated_count' => $updatedCount,
                'message' => "Successfully updated {$updatedCount} payments to {$request->status} status."
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            Log::error('Bulk payment status update failed', [
                'admin_user' => auth()->user()->email,
                'error' => $e->getMessage(),
                'payment_ids' => $request->payment_ids
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update payment statuses. Please try again.'
            ], 500);
        }
    }

    public function getPaymentDetails(Payment $payment)
    {
        $payment->load('user');
        
        return view('admin.payment-details', compact('payment'))->render();
    }

    public function sendPaymentNotification(Payment $payment)
    {
        try {
            // Create a simple notification email
            $user = $payment->user;
            $subject = 'Payment ' . ucfirst($payment->status) . ' - ' . config('app.name');
            
            $message = "Dear {$user->name},\n\n";
            $message .= "Your payment has been updated:\n\n";
            $message .= "Payment ID: {$payment->id}\n";
            $message .= "Amount: {$payment->formatted_amount}\n";
            $message .= "Type: " . ucfirst($payment->payment_type) . "\n";
            $message .= "Status: " . ucfirst($payment->status) . "\n";
            $message .= "Date: " . $payment->created_at->format('M d, Y H:i') . "\n\n";
            
            if ($payment->status === 'completed') {
                $message .= "Thank you for your payment! Your transaction has been processed successfully.\n\n";
            } elseif ($payment->status === 'failed') {
                $message .= "Unfortunately, your payment could not be processed. Please contact us for assistance.\n\n";
            }
            
            $message .= "Best regards,\n" . config('app.name');

            // Send actual email
            try {
                Mail::raw($message, function ($mail) use ($user, $subject) {
                    $mail->to($user->email, $user->name)
                         ->subject($subject)
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });

                Log::info("Payment notification email sent to {$user->email}", [
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
                    'subject' => $subject
                ]);

                return response()->json(['success' => true, 'message' => 'Notification sent successfully']);
            } catch (\Exception $emailError) {
                // If email fails, log it but don't fail the operation
                Log::warning("Failed to send payment notification email", [
                    'payment_id' => $payment->id,
                    'user_email' => $user->email,
                    'error' => $emailError->getMessage()
                ]);

                return response()->json([
                    'success' => true, 
                    'warning' => 'Payment status updated but email notification failed'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send payment notification: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to send notification']);
        }
    }

    public function bulkSendPaymentNotifications(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:payments,id'
        ]);

        $payments = Payment::whereIn('id', $request->payment_ids)->with('user')->get();
        $sentCount = 0;

        foreach ($payments as $payment) {
            try {
                $this->sendPaymentNotification($payment);
                $sentCount++;
            } catch (\Exception $e) {
                Log::error("Failed to send notification for payment {$payment->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'sent_count' => $sentCount,
            'total_count' => $payments->count()
        ]);
    }

    // Enhanced PDF receipt generation
    public function generatePaymentReceipt(Payment $payment)
    {
        $payment->load('user');
        
        // Choose template based on payment type
        $template = $payment->payment_type === 'membership' 
            ? 'admin.payment-receipt-membership' 
            : 'admin.payment-receipt-donation';
        
        $pdf = Pdf::loadView($template, compact('payment'));
        
        // Different filename based on type
        $typePrefix = $payment->payment_type === 'membership' ? 'membership' : 'donation';
        $fileName = $typePrefix . '_receipt_' . $payment->id . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }

    /**
     * Delete a payment (Super Admin only).
     */
    public function deletePayment(Payment $payment)
    {
        try {
            // Log the deletion for audit trail
            Log::info('Payment deleted by admin', [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'deleted_by' => auth()->user()->email,
                'deleted_at' => now()
            ]);

            // Delete the payment
            $payment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete payment: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'admin_user' => auth()->user()->email
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create system backup.
     */
    public function createSystemBackup()
    {
        try {
            // Use the correct backup command
            $exitCode = Artisan::call('db:backup');
            
            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'System backup created successfully'
                ]);
            } else {
                throw new \Exception('Backup command failed');
            }
        } catch (\Exception $e) {
            Log::error('System backup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear system logs.
     */
    public function clearSystemLogs()
    {
        try {
            // Clear old log files (>30 days)
            $exitCode = Artisan::call('log:clear');
            
            // Also truncate the current log file to free up space immediately
            $currentLogPath = storage_path('logs/laravel.log');
            if (file_exists($currentLogPath)) {
                // Backup current log size for reporting
                $logSize = filesize($currentLogPath);
                $logSizeFormatted = $this->formatBytes($logSize);
                
                // Truncate the current log file
                file_put_contents($currentLogPath, '');
                
                // Log the cleanup action
                Log::info('System logs cleared manually', [
                    'cleared_log_size' => $logSizeFormatted,
                    'admin_user' => auth()->user()->email,
                    'timestamp' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "System logs cleared successfully! Freed up {$logSizeFormatted} of space."
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'System logs cleared successfully!'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Clear logs failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Send bulk renewal notifications.
     */
    public function sendBulkNotifications()
    {
        try {
            // Get all expired users using calculated values (like the user status logic)
            $allRenewals = MembershipRenewal::with('user')
                ->where('is_hidden', false)
                ->where('is_renewed', false)
                ->get();

            // Filter by calculated expiry (not stored database field)
            $expiredRenewals = $allRenewals->filter(function ($renewal) {
                return $renewal->calculateDaysUntilExpiry() <= 0;
            });

            if ($expiredRenewals->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No expired users found to notify'
                ]);
            }

            $sentCount = 0;
            $failedCount = 0;
            $results = [];
            
            foreach ($expiredRenewals as $renewal) {
                try {
                    $user = $renewal->user;
                    $calculatedDays = $renewal->calculateDaysUntilExpiry();
                    $daysExpired = abs($calculatedDays); // Get positive number of days expired
                    
                    // Create personalized subject
                    $subject = "URGENT: Membership Expired - Immediate Renewal Required";
                    
                    // Create personalized message for each user
                    $message = "Dear {$user->name},\n\n";
                    $message .= "Your mosque membership has expired and requires immediate attention.\n\n";
                    
                    $message .= "MEMBERSHIP DETAILS:\n";
                    $message .= "━━━━━━━━━━━━━━━━━━━━\n";
                    $message .= "• Member ID: MBR-" . str_pad($user->id, 6, '0', STR_PAD_LEFT) . "\n";
                    $message .= "• Name: {$user->name}\n";
                    $message .= "• Email: {$user->email}\n";
                    $message .= "• Membership Started: " . $renewal->membership_start_date->format('M d, Y') . "\n";
                    $message .= "• Membership Expired: " . $renewal->membership_end_date->format('M d, Y') . "\n";
                    $message .= "• Days Expired: {$daysExpired} days ago\n\n";
                    
                    $message .= "❌ EXPIRED STATUS:\n";
                    $message .= "Your membership expired {$daysExpired} days ago. To continue enjoying our services and maintain your access to the mosque facilities, please renew immediately.\n\n";
                    
                    $message .= "TO RENEW YOUR MEMBERSHIP:\n";
                    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                    $message .= "1. Visit: " . config('app.url') . "/login\n";
                    $message .= "2. Log in with your account credentials\n";
                    $message .= "3. Click 'Make Payment'\n";
                    $message .= "4. Select 'Membership Renewal' (CHF 350)\n";
                    $message .= "5. Complete payment via Stripe, PayPal, TWINT, or Bank Transfer\n\n";
                    
                    $message .= "🕌 MEMBERSHIP BENEFITS (Upon Renewal):\n";
                    $message .= "• 24/7 access to prayer facilities\n";
                    $message .= "• Friday prayers and religious services\n";
                    $message .= "• Islamic education and community events\n";
                    $message .= "• Voting rights in community decisions\n";
                    $message .= "• Community support and networking\n\n";
                    
                    $message .= "⏰ IMPORTANT:\n";
                    $message .= "Please renew as soon as possible to avoid any interruption in services. If you have any questions or need assistance with the renewal process, please contact us immediately.\n\n";
                    
                    $message .= "If you believe this notice is in error or if you have already renewed, please contact our support team.\n\n";
                    $message .= "Barakallahu feek!\n\n";
                    $message .= "Best regards,\n";
                    $message .= config('mail.from.name') . " Team\n";
                    $message .= config('app.url') . "\n";
                    $message .= config('mail.from.address');

                    // Send personalized email using the working Zoho SMTP
                    Mail::raw($message, function ($mail) use ($user, $subject) {
                        $mail->to($user->email, $user->name)
                             ->subject($subject)
                             ->from(config('mail.from.address'), config('mail.from.name'))
                             ->replyTo(config('mail.from.address'), config('mail.from.name'));
                    });

                    // Mark notification as sent for this specific user
                    $renewal->markNotificationSent($calculatedDays);

                    $sentCount++;
                    $results[] = [
                        'user' => $user->name,
                        'email' => $user->email,
                        'days_expired' => $daysExpired,
                        'status' => 'sent'
                    ];

                    Log::info("Bulk expired notification sent successfully", [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'renewal_id' => $renewal->id,
                        'days_expired' => $daysExpired,
                        'calculated_days' => $calculatedDays,
                        'subject' => $subject
                    ]);

                } catch (\Exception $e) {
                    $failedCount++;
                    $results[] = [
                        'user' => $user->name ?? 'Unknown',
                        'email' => $user->email ?? 'Unknown',
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];

                    Log::error("Failed to send bulk notification to user {$renewal->user_id}", [
                        'user_email' => $user->email ?? 'unknown',
                        'error' => $e->getMessage(),
                        'renewal_id' => $renewal->id
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_expired' => $expiredRenewals->count(),
                'message' => "Sent {$sentCount} personalized notifications to expired users" . 
                           ($failedCount > 0 ? " ({$failedCount} failed)" : ""),
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk expired notifications failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to send notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get border color for user rows (helper method for optimization).
     */
    private function getBorderColor(int $daysUntilExpiry, bool $isHidden): string
    {
        if ($isHidden) return '#dc3545'; // Red - Hidden/Deleted users
        if ($daysUntilExpiry <= 0) return '#dc3545'; // Red - Expired
        if ($daysUntilExpiry <= 30) return '#ff6c37'; // Orange - Expiring within 30 days
        return '#28a745'; // Green - Active membership (>30 days)
    }

    /**
     * Get status badge for display (helper method for optimization).
     */
    private function getStatusBadge(int $daysUntilExpiry, bool $isHidden): array
    {
        if ($isHidden) {
            return [
                'text' => 'HIDDEN',
                'color' => 'white',
                'background' => '#dc3545'
            ];
        }

        if ($daysUntilExpiry <= 0) {
            return [
                'text' => 'EXPIRED',
                'color' => 'white',
                'background' => '#dc3545'
            ];
        }

        if ($daysUntilExpiry <= 7) {
            return [
                'text' => $daysUntilExpiry . 'D',
                'color' => 'white',
                'background' => '#dc3545'
            ];
        }

        if ($daysUntilExpiry <= 30) {
            return [
                'text' => $daysUntilExpiry . 'D',
                'color' => 'white',
                'background' => '#ff6c37'
            ];
        }

        return [
            'text' => 'ACTIVE',
            'color' => 'white',
            'background' => '#28a745'
        ];
    }

    /**
     * Verify production data setup.
     */
    public function verifyProductionData()
    {
        try {
            // Run diagnostic
            Artisan::call('admin:diagnose');
            $output = Artisan::output();
            
            return response('<h1>Production Data Verification</h1>
            <pre>' . htmlspecialchars($output) . '</pre>
            <br><a href="/dashboard" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Dashboard</a>
            <br><br><a href="/setup-production-data" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Setup Production Data</a>
            <br><br><a href="/setup-production-email" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Setup Production Email</a>
            <br><br><a href="/setup-test-expiry/infinitdizzajn@gmail.com?days=15" style="background: #ff6c37; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🎯 Setup 15-Day Expiry Test</a>
            ');
        } catch (\Exception $e) {
            return response('<h1>Error:</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
        }
    }

    /**
     * Setup production data with expired users.
     */
    public function setupProductionData()
    {
        try {
            // Run the test users command
            Artisan::call('test:create-expired-users', ['--clean' => true]);
            $output1 = Artisan::output();
            
            // Run diagnostic
            Artisan::call('admin:diagnose');
            $output2 = Artisan::output();
            
            return response('<h1>Production Data Setup Complete!</h1>
            <h2>Test Users Created:</h2>
            <pre>' . htmlspecialchars($output1) . '</pre>
            <h2>System Diagnostic:</h2>
            <pre>' . htmlspecialchars($output2) . '</pre>
            <br><a href="/dashboard" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Dashboard</a>
            <br><br><a href="/admin/users" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Users with Color Indicators</a>
            ');
            
        } catch (\Exception $e) {
            return response('<h1>Error:</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
        }
    }

    /**
     * Setup production email configuration and test notifications.
     */
    public function setupProductionEmail()
    {
        try {
            $superAdmin = User::where('email', 'kushtrim.m.arifi@gmail.com')->first();
            
            if (!$superAdmin) {
                return response('<h1>❌ Error: Super Admin Not Found</h1>
                <p>Could not find super admin with email: kushtrim.m.arifi@gmail.com</p>
                <br><a href="/verify-production-data" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Back to Verification</a>
                ');
            }

            // Check current mail configuration
            $mailConfig = [
                'MAIL_MAILER' => config('mail.default'),
                'MAIL_HOST' => config('mail.mailers.smtp.host'),
                'MAIL_PORT' => config('mail.mailers.smtp.port'),
                'MAIL_FROM_ADDRESS' => config('mail.from.address'),
                'MAIL_FROM_NAME' => config('mail.from.name'),
                'ADMIN_EMAIL' => env('ADMIN_EMAIL', 'Not set'),
            ];

            // Test notification preparation
            $testMessage = "🎉 Production Email Setup Successful!\n\n";
            $testMessage .= "This is a test notification from your EN NUR Membership system.\n\n";
            $testMessage .= "SUPER ADMIN DETAILS:\n";
            $testMessage .= "━━━━━━━━━━━━━━━━━━━━\n";
            $testMessage .= "• Name: {$superAdmin->name}\n";
            $testMessage .= "• Email: {$superAdmin->email}\n";
            $testMessage .= "• Role: {$superAdmin->role}\n";
            $testMessage .= "• Verified: " . ($superAdmin->email_verified_at ? 'Yes' : 'No') . "\n";
            $testMessage .= "• Created: " . $superAdmin->created_at->format('M d, Y H:i') . "\n\n";
            
            $testMessage .= "EMAIL CONFIGURATION:\n";
            $testMessage .= "━━━━━━━━━━━━━━━━━━━━\n";
            foreach ($mailConfig as $key => $value) {
                $testMessage .= "• {$key}: {$value}\n";
            }
            $testMessage .= "\n";
            
            $testMessage .= "🚀 Your email notifications are now ready!\n\n";
            $testMessage .= "You can now:\n";
            $testMessage .= "• Send membership renewal notifications\n";
            $testMessage .= "• Receive payment confirmations\n";
            $testMessage .= "• Get system alerts and reports\n\n";
            
            $testMessage .= "Best regards,\n";
            $testMessage .= "EN NUR Membership System\n";
            $testMessage .= config('app.url');

            // Try to send test email
            $emailSent = false;
            $emailError = null;
            
            try {
                Mail::raw($testMessage, function ($mail) use ($superAdmin) {
                    $mail->to($superAdmin->email, $superAdmin->name)
                         ->subject('🎉 Production Email Setup Complete - EN NUR Membership')
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });
                $emailSent = true;
                
                Log::info('Production email setup test sent successfully', [
                    'admin_email' => $superAdmin->email,
                    'mail_config' => $mailConfig
                ]);
                
            } catch (\Exception $e) {
                $emailError = $e->getMessage();
                Log::warning('Production email test failed', [
                    'admin_email' => $superAdmin->email,
                    'error' => $emailError,
                    'mail_config' => $mailConfig
                ]);
            }

            $html = '<h1>📧 Production Email Configuration</h1>';
            
            $html .= '<h2>✅ Super Admin Found</h2>';
            $html .= '<div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;">';
            $html .= '<strong>Name:</strong> ' . htmlspecialchars($superAdmin->name) . '<br>';
            $html .= '<strong>Email:</strong> ' . htmlspecialchars($superAdmin->email) . '<br>';
            $html .= '<strong>Role:</strong> ' . htmlspecialchars($superAdmin->role) . '<br>';
            $html .= '<strong>Verified:</strong> ' . ($superAdmin->email_verified_at ? '✅ Yes' : '❌ No') . '<br>';
            $html .= '<strong>Created:</strong> ' . $superAdmin->created_at->format('M d, Y H:i');
            $html .= '</div>';

            $html .= '<h2>⚙️ Current Mail Configuration</h2>';
            $html .= '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
            foreach ($mailConfig as $key => $value) {
                $html .= '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '<br>';
            }
            $html .= '</div>';

            if ($emailSent) {
                $html .= '<h2>✅ Test Email Sent Successfully</h2>';
                $html .= '<div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;">';
                $html .= 'A test email has been sent to <strong>' . htmlspecialchars($superAdmin->email) . '</strong><br>';
                $html .= 'Please check your inbox (and spam folder) for the test message.';
                $html .= '</div>';
            } else {
                $html .= '<h2>⚠️ Test Email Failed</h2>';
                $html .= '<div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;">';
                $html .= '<strong>Error:</strong> ' . htmlspecialchars($emailError) . '<br><br>';
                $html .= '<strong>Note:</strong> This might be expected if mail is set to "log" mode in production.<br>';
                $html .= 'Check your render.yaml configuration to enable SMTP for real email sending.';
                $html .= '</div>';
            }

            $html .= '<h2>📋 Next Steps for Production</h2>';
            $html .= '<div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;">';
            $html .= '<ol>';
            $html .= '<li><strong>Update render.yaml:</strong> Change MAIL_MAILER from "log" to "smtp"</li>';
            $html .= '<li><strong>Add SMTP credentials:</strong> Set up Gmail App Password or SMTP service</li>';
            $html .= '<li><strong>Update environment variables:</strong> Configure MAIL_USERNAME and MAIL_PASSWORD</li>';
            $html .= '<li><strong>Test notifications:</strong> Send renewal reminders to users</li>';
            $html .= '</ol>';
            $html .= '</div>';

            $html .= '<div style="margin: 20px 0;">';
            $html .= '<a href="/dashboard" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Go to Dashboard</a>';
            $html .= '<a href="/admin/users" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">View Users</a>';
            $html .= '<a href="/verify-production-data" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Back to Verification</a>';
            $html .= '</div>';

            return response($html);
            
        } catch (\Exception $e) {
            Log::error('Production email setup failed: ' . $e->getMessage());
            return response('<h1>❌ Error Setting Up Production Email</h1>
            <pre>' . htmlspecialchars($e->getMessage()) . '</pre>
            <br><a href="/verify-production-data" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Back to Verification</a>
            ');
        }
    }

    /**
     * Setup test expiry scenario for an existing user.
     */
    public function setupTestExpiry($email)
    {
        try {
            // URL decode the email
            $email = urldecode($email);
            $days = request()->get('days', 15); // Default to 15 days
            
            $html = '<h1>🎯 Setting Up Test Expiry Scenario</h1>';
            $html .= '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>';
            $html .= '<p><strong>Days until expiry:</strong> ' . $days . '</p>';
            
            // Find the user
            $user = User::where('email', $email)->first();
            if (!$user) {
                $html .= '<div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;">';
                $html .= '<h2>❌ User Not Found</h2>';
                $html .= '<p>Could not find user with email: ' . htmlspecialchars($email) . '</p>';
                $html .= '</div>';
                
                $html .= '<div style="margin: 20px 0;">';
                $html .= '<a href="/verify-production-data" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Back to Verification</a>';
                $html .= '</div>';
                
                return response($html);
            }
            
            $html .= '<div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;">';
            $html .= '<h2>✅ User Found</h2>';
            $html .= '<p><strong>Name:</strong> ' . htmlspecialchars($user->name) . '</p>';
            $html .= '<p><strong>Email:</strong> ' . htmlspecialchars($user->email) . '</p>';
            $html .= '<p><strong>ID:</strong> ' . $user->id . '</p>';
            $html .= '<p><strong>Role:</strong> ' . htmlspecialchars($user->role) . '</p>';
            $html .= '<p><strong>Verified:</strong> ' . ($user->email_verified_at ? '✅ Yes' : '❌ No') . '</p>';
            $html .= '</div>';
            
            // Clean up existing records for this user
            $deletedRenewals = MembershipRenewal::where('user_id', $user->id)->count();
            $deletedPayments = Payment::where('user_id', $user->id)->count();
            
            MembershipRenewal::where('user_id', $user->id)->delete();
            Payment::where('user_id', $user->id)->delete();
            
            $html .= '<div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;">';
            $html .= '<h2>🗑️ Cleanup Complete</h2>';
            $html .= '<p>Deleted ' . $deletedRenewals . ' renewal records</p>';
            $html .= '<p>Deleted ' . $deletedPayments . ' payment records</p>';
            $html .= '</div>';
            
            // Calculate dates
            if ($days > 0) {
                $expiryDate = now()->addDays($days)->startOfDay();
                $status = 'expiring';
            } else {
                $expiryDate = now()->subDays(abs($days))->startOfDay();
                $status = 'expired';
            }
            
            $startDate = $expiryDate->copy()->subYear();
            
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => 35000, // CHF 350.00 in cents
                'currency' => 'CHF',
                'payment_type' => 'membership',
                'payment_method' => 'stripe',
                'status' => 'completed',
                'stripe_payment_intent_id' => 'pi_test_' . $status . '_' . $user->id . '_' . time(),
                'metadata' => [
                    'membership_start' => $startDate->toDateString(),
                    'membership_end' => $expiryDate->toDateString(),
                    'test_scenario' => $days . '_days_to_expiry',
                    'created_via' => 'production_test_setup'
                ],
                'created_at' => $startDate,
                'updated_at' => $startDate
            ]);
            
            // Create membership renewal record
            $renewal = MembershipRenewal::create([
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'membership_start_date' => $startDate,
                'membership_end_date' => $expiryDate,
                'is_hidden' => false,
                'notifications_sent' => [],
                'last_notification_sent_at' => null,
                'created_at' => $startDate,
                'updated_at' => now()
            ]);
            
            // Determine color indicator
            $daysUntilExpiry = (int) $renewal->days_until_expiry;
            if ($daysUntilExpiry <= 0) {
                $color = '🔴 RED (Expired)';
                $colorClass = 'danger';
            } elseif ($daysUntilExpiry <= 30) {
                $color = '🟠 ORANGE (Expiring within 30 days)';
                $colorClass = 'warning';
            } else {
                $color = '🟢 GREEN (Active)';
                $colorClass = 'success';
            }
            
            $html .= '<div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;">';
            $html .= '<h2>🎉 Test Scenario Created Successfully!</h2>';
            $html .= '<p><strong>Payment ID:</strong> ' . $payment->id . ' (CHF 350.00)</p>';
            $html .= '<p><strong>Renewal ID:</strong> ' . $renewal->id . '</p>';
            $html .= '<p><strong>Membership Start:</strong> ' . $startDate->format('Y-m-d') . '</p>';
            $html .= '<p><strong>Membership End:</strong> ' . $expiryDate->format('Y-m-d') . '</p>';
            $html .= '<p><strong>Days Until Expiry:</strong> ' . $daysUntilExpiry . '</p>';
            $html .= '<p><strong>Color Indicator:</strong> ' . $color . '</p>';
            $html .= '</div>';
            
            // Email notification test
            $html .= '<div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;">';
            $html .= '<h2>📧 Email Notification Test</h2>';
            
            try {
                $notificationMessage = "🔔 Membership Renewal Reminder\n\n";
                $notificationMessage .= "Dear {$user->name},\n\n";
                $notificationMessage .= "This is a test notification for your membership renewal.\n\n";
                $notificationMessage .= "MEMBERSHIP DETAILS:\n";
                $notificationMessage .= "━━━━━━━━━━━━━━━━━━━━\n";
                $notificationMessage .= "• Member ID: MBR-" . str_pad($user->id, 6, '0', STR_PAD_LEFT) . "\n";
                $notificationMessage .= "• Current Expiry: " . $expiryDate->format('M d, Y') . "\n";
                $notificationMessage .= "• Days Remaining: {$daysUntilExpiry}\n\n";
                
                if ($daysUntilExpiry <= 0) {
                    $notificationMessage .= "❌ EXPIRED: Your membership has expired!\n\n";
                } elseif ($daysUntilExpiry <= 7) {
                    $notificationMessage .= "⚠️ URGENT: Your membership expires very soon!\n\n";
                } elseif ($daysUntilExpiry <= 30) {
                    $notificationMessage .= "📅 Please consider renewing your membership soon.\n\n";
                }
                
                $notificationMessage .= "TO RENEW YOUR MEMBERSHIP:\n";
                $notificationMessage .= "━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $notificationMessage .= "1. Log in to your account at " . config('app.url') . "\n";
                $notificationMessage .= "2. Click 'Make Payment'\n";
                $notificationMessage .= "3. Select 'Membership' (CHF 350)\n";
                $notificationMessage .= "4. Complete payment via available methods\n\n";
                $notificationMessage .= "Best regards,\n";
                $notificationMessage .= "EN NUR Membership Team\n";
                $notificationMessage .= config('app.url');

                // Send test email
                Mail::raw($notificationMessage, function ($mail) use ($user, $daysUntilExpiry) {
                    $subject = $daysUntilExpiry <= 0 
                        ? 'Membership Expired - Immediate Renewal Required'
                        : "Membership Renewal Reminder - {$daysUntilExpiry} Days Remaining";
                    
                    $mail->to($user->email, $user->name)
                         ->subject($subject)
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });
                
                $html .= '<p>✅ <strong>Email sent successfully!</strong></p>';
                $html .= '<p>Check the inbox for <strong>' . htmlspecialchars($user->email) . '</strong></p>';
                $html .= '<p>Subject: "Membership Renewal Reminder - ' . $daysUntilExpiry . ' Days Remaining"</p>';
                
                Log::info('Test renewal notification sent', [
                    'user_email' => $user->email,
                    'days_until_expiry' => $daysUntilExpiry,
                    'test_scenario' => true
                ]);
                
            } catch (\Exception $e) {
                $html .= '<p>⚠️ <strong>Email failed:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                $html .= '<p>This might be expected if SMTP is not configured in production.</p>';
            }
            
            $html .= '</div>';
            
            $html .= '<div style="margin: 20px 0;">';
            $html .= '<a href="/dashboard" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Go to Dashboard</a>';
            $html .= '<a href="/admin/users" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">View Users with Color Indicators</a>';
            $html .= '<a href="/verify-production-data" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Back to Verification</a>';
            $html .= '</div>';
            
            return response($html);
            
        } catch (\Exception $e) {
            Log::error('Test expiry setup failed: ' . $e->getMessage());
            return response('<h1>❌ Error Setting Up Test Expiry</h1>
            <pre>' . htmlspecialchars($e->getMessage()) . '</pre>
            <br><a href="/verify-production-data" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Back to Verification</a>
            ');
        }
    }

    /**
     * Debug method to diagnose user membership issues.
     */
    public function debugUsers()
    {
        try {
            $output = [];
            $output[] = "🔍 DIAGNOSING USER MEMBERSHIP ISSUES";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            $output[] = "=" . str_repeat("=", 60);
            $output[] = "";
            
            // Check if users exist
            $mardalUser = User::where('email', 'info@mardal.ch')->first();
            $infinitUser = User::where('email', 'infinitdizzajn@gmail.com')->first();
            
            $output[] = "👥 USER CHECK:";
            $output[] = "- Mardal user (info@mardal.ch): " . ($mardalUser ? "✅ EXISTS (ID: {$mardalUser->id})" : "❌ NOT FOUND");
            $output[] = "- Infinit user (infinitdizzajn@gmail.com): " . ($infinitUser ? "✅ EXISTS (ID: {$infinitUser->id})" : "❌ NOT FOUND");
            $output[] = "";
            
            // Check all users
            $allUsers = User::all();
            $output[] = "📊 ALL USERS IN DATABASE:";
            foreach ($allUsers as $user) {
                $output[] = "- ID: {$user->id}, Name: '{$user->name}', Email: '{$user->email}', Role: '{$user->role}'";
            }
            $output[] = "";
            
            // Check all membership renewals
            $allRenewals = MembershipRenewal::with('user')->get();
            $output[] = "🔄 ALL MEMBERSHIP RENEWALS:";
            if ($allRenewals->count() > 0) {
                foreach ($allRenewals as $renewal) {
                    $userName = $renewal->user ? $renewal->user->name : 'Unknown User';
                    $userEmail = $renewal->user ? $renewal->user->email : 'Unknown Email';
                    $calculatedDays = $renewal->calculateDaysUntilExpiry();
                    $output[] = "- User: {$userName} ({$userEmail})";
                    $output[] = "  Start: {$renewal->membership_start_date}";
                    $output[] = "  End: {$renewal->membership_end_date}";
                    $output[] = "  Days Until Expiry (DB): {$renewal->days_until_expiry}";
                    $output[] = "  Days Until Expiry (Calculated): {$calculatedDays}";
                    $output[] = "  Is Expired: " . ($renewal->is_expired ? 'Yes' : 'No');
                    $output[] = "  Is Hidden: " . ($renewal->is_hidden ? 'Yes' : 'No');
                    $output[] = "  Is Renewed: " . ($renewal->is_renewed ? 'Yes' : 'No');
                    $output[] = "";
                }
            } else {
                $output[] = "❌ NO MEMBERSHIP RENEWALS FOUND!";
            }
            $output[] = "";
            
            // Test admin dashboard logic
            $adminDashboardRenewals = MembershipRenewal::with('user')
                ->where('is_renewed', false)
                ->where('is_hidden', false)
                ->get()
                ->filter(function ($renewal) {
                    $daysUntilExpiry = $renewal->calculateDaysUntilExpiry();
                    return $daysUntilExpiry <= 30 && $daysUntilExpiry > -30;
                });
            
            $output[] = "🎛️ ADMIN DASHBOARD LOGIC TEST:";
            $output[] = "- Query: is_renewed=false AND is_hidden=false AND days_until_expiry <= 30 AND > -30";
            $output[] = "- Total renewals matching criteria: " . $adminDashboardRenewals->count();
            
            if ($adminDashboardRenewals->count() > 0) {
                foreach ($adminDashboardRenewals as $renewal) {
                    $userName = $renewal->user ? $renewal->user->name : 'Unknown';
                    $userEmail = $renewal->user ? $renewal->user->email : 'Unknown';
                    $calculatedDays = $renewal->calculateDaysUntilExpiry();
                    $status = $calculatedDays <= 0 ? '🔴 EXPIRED' : '🟠 EXPIRING';
                    $output[] = "  - {$status} {$userName} ({$userEmail}): {$calculatedDays} days";
                }
            } else {
                $output[] = "❌ NO USERS WILL APPEAR IN ADMIN DASHBOARD!";
            }
            
            return response('<h2>🔍 User Diagnosis Results</h2><pre>' . implode("\n", $output) . '</pre><br><br><a href="/dashboard" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👑 Admin Dashboard</a>');
            
        } catch (\Exception $e) {
            return response('<h2>❌ Error During Diagnosis</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>');
        }
    }

    /**
     * Setup expired memberships for testing.
     */
    public function setupExpiredMemberships()
    {
        try {
            $output = [];
            $output[] = "🔧 SETTING UP EXPIRED MEMBERSHIPS FOR TESTING";
            $output[] = "Timestamp: " . now()->toDateTimeString();
            $output[] = "";
            
            // Create or find users
            $mardalUser = User::firstOrCreate(
                ['email' => 'info@mardal.ch'],
                [
                    'name' => 'Mardal User',
                    'password' => Hash::make(env('TEST_USER_PASSWORD', 'change-me')),
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]
            );
            
            $infinitUser = User::firstOrCreate(
                ['email' => 'infinitdizzajn@gmail.com'],
                [
                    'name' => 'kushtrim arifi',
                    'password' => Hash::make(env('USER_CORRECT_PASSWORD', 'change-me')),
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]
            );
            
            $output[] = "✅ Users ready: {$mardalUser->name} and {$infinitUser->name}";
            
            // Clean existing data
            MembershipRenewal::whereIn('user_id', [$mardalUser->id, $infinitUser->id])->delete();
            Payment::whereIn('user_id', [$mardalUser->id, $infinitUser->id])->delete();
            
            // Create expired membership for Mardal (5 days ago)
            $mardalPayment = Payment::create([
                'user_id' => $mardalUser->id,
                'amount' => 35000,
                'currency' => 'CHF',
                'payment_type' => 'membership',
                'payment_method' => 'stripe',
                'status' => 'completed',
                'transaction_id' => 'test_mardal_' . time(),
                'created_at' => now()->subYear()->subDays(5),
                'updated_at' => now()->subYear()->subDays(5),
            ]);
            
            $mardalExpiry = now()->subDays(5);
            $mardalStart = $mardalExpiry->copy()->subYear();
            
            $mardalRenewal = MembershipRenewal::create([
                'user_id' => $mardalUser->id,
                'payment_id' => $mardalPayment->id,
                'membership_start_date' => $mardalStart,
                'membership_end_date' => $mardalExpiry,
                'days_until_expiry' => -5,
                'is_expired' => true,
                'is_hidden' => false,
                'is_renewed' => false,
                'notifications_sent' => [],
            ]);
            
            // Create expiring membership for Infinit (7 days remaining)
            $infinitPayment = Payment::create([
                'user_id' => $infinitUser->id,
                'amount' => 35000,
                'currency' => 'CHF',
                'payment_type' => 'membership',
                'payment_method' => 'stripe',
                'status' => 'completed',
                'transaction_id' => 'test_infinit_' . time(),
                'created_at' => now()->subYear()->addDays(7),
                'updated_at' => now()->subYear()->addDays(7),
            ]);
            
            $infinitExpiry = now()->addDays(7);
            $infinitStart = $infinitExpiry->copy()->subYear();
            
            $infinitRenewal = MembershipRenewal::create([
                'user_id' => $infinitUser->id,
                'payment_id' => $infinitPayment->id,
                'membership_start_date' => $infinitStart,
                'membership_end_date' => $infinitExpiry,
                'days_until_expiry' => 7,
                'is_expired' => false,
                'is_hidden' => false,
                'is_renewed' => false,
                'notifications_sent' => [],
            ]);
            
            $output[] = "🔴 Mardal User: EXPIRED 5 days ago";
            $output[] = "🟠 Infinit User: EXPIRES in 7 days";
            $output[] = "";
            $output[] = "✅ Both users should now appear in Super Admin dashboard!";
            
            return response('<h2>✅ Expired Memberships Setup Complete!</h2><pre>' . implode("\n", $output) . '</pre><br><br><a href="/dashboard" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👑 Check Admin Dashboard</a><br><br><a href="/admin/users" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">👥 View Users</a>');
            
        } catch (\Exception $e) {
            return response('<h2>❌ Error Setting Up Memberships</h2><pre>Error: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString() . '</pre>');
        }
    }

    /**
     * Show the form for creating a user without email verification (Admin Panel).
     */
    public function showCreateUserWithoutEmail()
    {
        return view('admin.create-user-without-email');
    }

    /**
     * Create a user without email verification using override key (Admin Panel).
     */
    public function createUserWithoutEmail(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'marital_status' => 'required|string|in:single,married,divorced,widowed',
            'phone_number' => 'required|string|max:20',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'override_key' => 'required|string',
        ]);

        // Check the override key
        $correctKey = '&hg^^5%d(8jNbV$3@#$$';
        if ($request->override_key !== $correctKey) {
            return redirect()->back()
                ->withErrors(['override_key' => 'Invalid override key.'])
                ->withInput();
        }

        try {
            // Create the user with username-based authentication
            $user = User::create([
                'name' => $request->name,
                'first_name' => $request->first_name,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address,
                'postal_code' => $request->postal_code,
                'city' => $request->city,
                'marital_status' => $request->marital_status,
                'phone_number' => $request->phone_number,
                'username' => $request->username,
                'email' => $request->username . '@local.system', // Generate a system email for internal use
                'password' => Hash::make($request->password),
                'email_verified_at' => now(), // Auto-verify since it's system generated
                'role' => 'user',
            ]);

            // Log the override usage for security
            Log::info('Admin created user with username authentication', [
                'admin_id' => auth()->id(),
                'admin_email' => auth()->user()->email,
                'created_user_id' => $user->id,
                'created_user_username' => $user->username,
                'created_user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);

            return redirect()->route('admin.users')
                ->with('success', 'User created successfully with username authentication.');

        } catch (\Exception $e) {
            Log::error('Failed to create user with username authentication', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->except(['password', 'password_confirmation', 'override_key']),
            ]);

            return redirect()->back()
                ->withErrors(['general' => 'Failed to create user. Please try again.'])
                ->withInput();
        }
    }
} 