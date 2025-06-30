<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            // If already verified, check if terms are accepted
            if ($request->user()->hasAcceptedTerms()) {
                return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
            } else {
                return redirect()->route('terms.show')->with('success', 'Email verified! Please accept our terms to continue.');
            }
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // After email verification, redirect to terms acceptance
        return redirect()->route('terms.show')->with('success', 'Email verified successfully! Please accept our terms to continue.');
    }
} 