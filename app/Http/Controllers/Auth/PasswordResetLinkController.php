<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Users created via the admin "create user without email" form have
        // synthetic emails ending in @local.system — they have no real inbox
        // and cannot receive a reset link. Give them a clear message instead
        // of letting the generic "we sent you an email" response mislead them.
        // (Audit finding 2.15)
        if (str_ends_with(strtolower((string) $request->email), '@local.system')) {
            return back()->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'This account was created by an admin without email verification. Self-service password reset is not available. Please contact an administrator.',
                ]);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}
