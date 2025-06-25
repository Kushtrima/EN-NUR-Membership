<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MembershipRenewal;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MembershipRenewalController extends Controller
{
    /**
     * Send renewal notification email to user.
     */
    public function sendNotification(Request $request, MembershipRenewal $renewal)
    {
        // Only super admins can send notifications
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Send the notification email
            $this->sendRenewalNotificationEmail($renewal);
            
            // Mark notification as sent for current days remaining
            $renewal->markNotificationSent($renewal->days_until_expiry);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'days_remaining' => $renewal->days_until_expiry,
                'last_sent' => $renewal->last_notification_sent_at->format('M d, Y H:i')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send renewal notification', [
                'renewal_id' => $renewal->id,
                'user_id' => $renewal->user_id,
                'user_email' => $renewal->user->email ?? 'unknown',
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to send notification: ' . $e->getMessage(),
                'details' => get_class($e) . ' in ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    /**
     * Delete renewal from the notification list.
     */
    public function hide(Request $request, MembershipRenewal $renewal)
    {
        // Only super admins can delete renewals from dashboard
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $renewal->update([
            'is_hidden' => true,
            'admin_notes' => $request->input('reason', 'Removed from dashboard by admin')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Renewal removed from notification list'
        ]);
    }

    /**
     * Show hidden renewal back in the notification list.
     */
    public function show(Request $request, MembershipRenewal $renewal)
    {
        // Only super admins can show renewals
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $renewal->update([
            'is_hidden' => false,
            'admin_notes' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Renewal restored to notification list'
        ]);
    }

    /**
     * Get renewal details for display.
     */
    public function details(MembershipRenewal $renewal)
    {
        // Only super admins can view details
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'renewal' => $renewal->load('user', 'payment'),
            'notifications_sent' => $renewal->notifications_sent ?? [],
            'message' => $renewal->getNotificationMessage(),
            'priority_level' => $renewal->getPriorityLevel(),
            'display_class' => $renewal->getDisplayClass()
        ]);
    }

    /**
     * Send renewal notification email.
     */
    private function sendRenewalNotificationEmail(MembershipRenewal $renewal)
    {
        $user = $renewal->user;
        $daysRemaining = $renewal->days_until_expiry;
        $notificationMessage = $renewal->getNotificationMessage();

        // Email subject based on urgency
        if ($daysRemaining <= 0) {
            $subject = 'Membership Expired - Immediate Renewal Required';
        } elseif ($daysRemaining <= 1) {
            $subject = 'Membership Expires Tomorrow - Urgent Renewal Required';
        } elseif ($daysRemaining <= 7) {
            $subject = "Membership Expires in {$daysRemaining} Days - Renewal Required";
        } else {
            $subject = "Membership Renewal Reminder - {$daysRemaining} Days Remaining";
        }

        // Create simple email content
        $renewalUrl = route('payment.create');
        $membershipStart = $renewal->membership_start_date ? $renewal->membership_start_date->format('M d, Y') : 'N/A';
        $membershipEnd = $renewal->membership_end_date ? $renewal->membership_end_date->format('M d, Y') : 'N/A';
        
        $emailBody = "
Hello {$user->name},

{$notificationMessage}

Your Membership Details:
- Membership Start: {$membershipStart}
- Membership End: {$membershipEnd}
- Days Remaining: " . ($daysRemaining > 0 ? $daysRemaining : 'EXPIRED') . "

To renew your membership, please visit:
{$renewalUrl}

If you have any questions, please contact our support team.

Best regards,
EN NUR - MEMBERSHIP Team
        ";

        // Send simple text email
        Mail::raw($emailBody, function ($message) use ($user, $subject) {
            $message->to($user->email, $user->name)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }
}
