<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TermsController extends Controller
{
    /**
     * Show the terms and conditions acceptance page.
     */
    public function show()
    {
        // Redirect if user has already accepted terms
        if (auth()->user()->hasAcceptedTerms()) {
            return redirect()->route('dashboard');
        }

        return view('terms.accept');
    }

    /**
     * Handle terms and conditions acceptance.
     */
    public function accept(Request $request)
    {
        try {
            // Validate checkboxes
            $request->validate([
                'accept_terms' => 'required|accepted',
                'accept_privacy' => 'required|accepted',
            ], [
                'accept_terms.required' => 'You must accept the Terms and Conditions to continue.',
                'accept_terms.accepted' => 'You must accept the Terms and Conditions to continue.',
                'accept_privacy.required' => 'You must accept the Privacy Policy to continue.',
                'accept_privacy.accepted' => 'You must accept the Privacy Policy to continue.',
            ]);

            $user = auth()->user();
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please log in to continue.');
            }
            
            // Check if user has already accepted terms
            if ($user->hasAcceptedTerms()) {
                return redirect()->route('dashboard')->with('info', 'You have already accepted the terms.');
            }
            
            // Update terms acceptance using direct database update
            \DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'terms_accepted_at' => now(),
                    'terms_version' => '1.0',
                    'terms_accepted_ip' => $request->ip(),
                    'updated_at' => now(),
                ]);

            // Clear any cached user data
            auth()->user()->refresh();

            // Send notification email to super admin
            $this->sendNewUserNotification($user);

            // Redirect to dashboard with success message
            return redirect()->route('dashboard')->with('success', 'Welcome! Your account is now fully activated.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Terms acceptance error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email ?? 'unknown',
                'error' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred while processing your request. Please try again.');
        }
    }

    /**
     * Show the full terms and conditions page.
     */
    public function terms()
    {
        return view('terms.full');
    }

    /**
     * Show the privacy policy page.
     */
    public function privacy()
    {
        return view('terms.privacy');
    }

    /**
     * Send notification email to super admin about new user activation.
     */
    private function sendNewUserNotification($user)
    {
        try {
            $adminEmail = 'info@xhamia-en-nur.ch';
            
            // Send email notification
            Mail::send('emails.new-user-activated', [
                'user' => $user,
                'activatedAt' => now(),
                'userAgent' => request()->userAgent(),
                'ipAddress' => request()->ip()
            ], function ($message) use ($adminEmail, $user) {
                $message->to($adminEmail)
                        ->subject('🎉 New User Activated - ' . $user->name)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            \Log::info('New user activation notification sent', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'admin_email' => $adminEmail
            ]);

        } catch (\Exception $e) {
            // Don't fail the terms acceptance if email fails
            \Log::error('Failed to send new user notification email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getTraceAsString()
            ]);
        }
    }
}
