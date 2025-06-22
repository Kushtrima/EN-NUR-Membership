<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PaymentExportController extends Controller
{
    /**
     * Export user's own payments to PDF.
     */
    public function exportUserPayments(Request $request)
    {
        $user = auth()->user();
        return $this->generatePaymentsPDF($user, $request);
    }

    /**
     * Export any user's payments to PDF (Super Admin only).
     */
    public function exportAdminPayments(Request $request, User $user)
    {
        // Only super admin can export other users' payments
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Only super administrators can export other users\' payments.');
        }

        return $this->generatePaymentsPDF($user, $request);
    }

    /**
     * Export all payments from all users to PDF (Super Admin only).
     */
    public function exportAllPayments(Request $request)
    {
        // Only super admin can export all payments
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Only super administrators can export all payments.');
        }

        return $this->generateAllPaymentsPDF($request);
    }

    /**
     * Generate PDF for user payments.
     */
    private function generatePaymentsPDF(User $user, Request $request)
    {
        // Validate date range if provided
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:pending,completed,failed,cancelled',
            'type' => 'nullable|in:membership,donation'
        ]);

        // Build query
        $query = $user->payments()->with('user');

        // Apply filters
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('payment_type', $request->type);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Calculate totals
        $totalAmount = $payments->where('status', Payment::STATUS_COMPLETED)->sum('amount') / 100;
        $membershipTotal = $payments->where('status', Payment::STATUS_COMPLETED)
                                  ->where('payment_type', 'membership')->sum('amount') / 100;
        $donationTotal = $payments->where('status', Payment::STATUS_COMPLETED)
                                ->where('payment_type', 'donation')->sum('amount') / 100;

        // Prepare data for PDF
        $data = [
            'user' => $user,
            'payments' => $payments,
            'totalAmount' => $totalAmount,
            'membershipTotal' => $membershipTotal,
            'donationTotal' => $donationTotal,
            'filters' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'type' => $request->type,
            ],
            'exportDate' => now(),
            'exportedBy' => auth()->user(),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('exports.payments-pdf', $data);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Generate filename
        $filename = 'payments_' . $user->name . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        $filename = str_replace(' ', '_', $filename);

        return $pdf->download($filename);
    }

    /**
     * Generate PDF for all payments from all users.
     */
    private function generateAllPaymentsPDF(Request $request)
    {
        // Build query for all payments
        $query = Payment::with('user');

        // Apply filters if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_type') && $request->payment_type !== '') {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('payment_method') && $request->payment_method !== '') {
            $query->where('payment_method', $request->payment_method);
        }

        // Get payments ordered by date (newest first)
        $payments = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary statistics
        $totalPayments = $payments->count();
        $completedPayments = $payments->where('status', Payment::STATUS_COMPLETED);
        $totalAmount = $completedPayments->sum('amount') / 100;
        $totalUsers = $payments->pluck('user_id')->unique()->count();

        // Group payments by status
        $paymentsByStatus = $payments->groupBy('status');
        $paymentsByType = $payments->groupBy('payment_type');
        $paymentsByMethod = $payments->groupBy('payment_method');

        // Prepare data for PDF
        $data = [
            'payments' => $payments,
            'totalPayments' => $totalPayments,
            'completedPayments' => $completedPayments->count(),
            'totalAmount' => $totalAmount,
            'totalUsers' => $totalUsers,
            'paymentsByStatus' => $paymentsByStatus,
            'paymentsByType' => $paymentsByType,
            'paymentsByMethod' => $paymentsByMethod,
            'filters' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
            ],
            'exportedBy' => auth()->user(),
            'exportDate' => now(),
            'isAllPaymentsExport' => true,
        ];

        // Generate PDF
        $pdf = Pdf::loadView('exports.all-payments-pdf', $data);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Generate filename
        $filename = 'all_payments_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show export form for user's own payments.
     */
    public function showUserExportForm()
    {
        $user = auth()->user();
        $paymentStats = [
            'total_payments' => $user->payments()->count(),
            'completed_payments' => $user->payments()->where('status', Payment::STATUS_COMPLETED)->count(),
            'total_amount' => $user->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount') / 100,
            'membership_payments' => $user->payments()->where('payment_type', 'membership')->where('status', Payment::STATUS_COMPLETED)->count(),
            'donation_payments' => $user->payments()->where('payment_type', 'donation')->where('status', Payment::STATUS_COMPLETED)->count(),
            'first_payment' => $user->payments()->oldest()->first(),
            'latest_payment' => $user->payments()->latest()->first(),
        ];

        return view('exports.user-payments-form', compact('paymentStats'));
    }

    /**
     * Show export form for admin (any user's payments).
     */
    public function showAdminExportForm(User $user)
    {
        // Only super admin can access this
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Only super administrators can export other users\' payments.');
        }

        $paymentStats = [
            'total_payments' => $user->payments()->count(),
            'completed_payments' => $user->payments()->where('status', Payment::STATUS_COMPLETED)->count(),
            'total_amount' => $user->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount') / 100,
            'membership_payments' => $user->payments()->where('payment_type', 'membership')->where('status', Payment::STATUS_COMPLETED)->count(),
            'donation_payments' => $user->payments()->where('payment_type', 'donation')->where('status', Payment::STATUS_COMPLETED)->count(),
            'first_payment' => $user->payments()->oldest()->first(),
            'latest_payment' => $user->payments()->latest()->first(),
        ];

        return view('exports.admin-payments-form', compact('user', 'paymentStats'));
    }

    /**
     * Show export form for all payments from all users (Super Admin only).
     */
    public function showAllPaymentsExportForm()
    {
        // Only super admin can access this
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Only super administrators can export all payments.');
        }

        // Calculate overall statistics
        $allPaymentStats = [
            'total_payments' => Payment::count(),
            'completed_payments' => Payment::where('status', Payment::STATUS_COMPLETED)->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'failed_payments' => Payment::where('status', 'failed')->count(),
            'cancelled_payments' => Payment::where('status', 'cancelled')->count(),
            'total_amount' => Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount') / 100,
            'membership_payments' => Payment::where('payment_type', 'membership')->where('status', Payment::STATUS_COMPLETED)->count(),
            'donation_payments' => Payment::where('payment_type', 'donation')->where('status', Payment::STATUS_COMPLETED)->count(),
            'total_users' => Payment::distinct('user_id')->count(),
            'first_payment' => Payment::oldest()->first(),
            'latest_payment' => Payment::latest()->first(),
            'payments_by_method' => [
                'stripe' => Payment::where('payment_method', 'stripe')->count(),
                'paypal' => Payment::where('payment_method', 'paypal')->count(),
                'twint' => Payment::where('payment_method', 'twint')->count(),
                'bank_transfer' => Payment::where('payment_method', 'bank_transfer')->count(),
            ],
        ];

        return view('exports.all-payments-form', compact('allPaymentStats'));
    }
} 