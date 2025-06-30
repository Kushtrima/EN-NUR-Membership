<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $request->validate([
            'accept_terms' => 'required|accepted',
            'accept_privacy' => 'required|accepted',
        ], [
            'accept_terms.required' => 'You must accept the Terms and Conditions to continue.',
            'accept_terms.accepted' => 'You must accept the Terms and Conditions to continue.',
            'accept_privacy.required' => 'You must accept the Privacy Policy to continue.',
            'accept_privacy.accepted' => 'You must accept the Privacy Policy to continue.',
        ]);

        // Accept terms with current version and IP
        auth()->user()->acceptTerms('1.0', $request->ip());

        // Redirect to dashboard with success message
        return redirect()->route('dashboard')->with('success', 'Welcome! Your account is now fully activated.');
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
}
