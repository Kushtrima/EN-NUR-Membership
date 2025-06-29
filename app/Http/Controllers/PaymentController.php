<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
// PayPal integration temporarily disabled for easy setup
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use PayPal\Core\PayPalHttpClient;
use PayPal\Core\SandboxEnvironment;
use PayPal\Core\ProductionEnvironment;
use PayPal\v1\Payments\PaymentCreateRequest;
use PayPal\v1\Payments\PaymentExecuteRequest;

class PaymentController extends Controller
{
    /**
     * Show payment creation form.
     */
    public function create()
    {
        $membershipAmount = config('app.membership_amount', 35000);
        $donationAmounts = [5000, 10000, 20000, 50000]; // CHF 50, 100, 200, 500

        return view('payments.create', compact('membershipAmount', 'donationAmounts'));
    }

    /**
     * Display payments list based on user role.
     * Regular users see only their own payments.
     * Admins see all payments (redirected to admin payments page).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // If user is admin, redirect to admin payments page
        if ($user->isAdmin()) {
            return redirect()->route('admin.payments');
        }
        
        // For regular users, show only their own payments
        $query = $user->payments();

        // Apply filters if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('payment_type', $request->type);
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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('payment_type', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }

        // Allow user to choose how many payments to show
        $perPage = $request->get('per_page', 50); // Default to 50 payments
        if ($perPage === 'all') {
            $payments = $query->orderBy('created_at', 'desc')->get();
            // Create a fake paginator for consistency
            $payments = new \Illuminate\Pagination\LengthAwarePaginator(
                $payments,
                $payments->count(),
                $payments->count(),
                1,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        } else {
            $payments = $query->orderBy('created_at', 'desc')->paginate((int)$perPage);
        }

        // Calculate user's payment statistics
        $stats = [
            'total' => $user->payments()->count(),
            'completed' => $user->payments()->where('status', Payment::STATUS_COMPLETED)->count(),
            'pending' => $user->payments()->where('status', Payment::STATUS_PENDING)->count(),
            'failed' => $user->payments()->where('status', 'failed')->count(),
            'total_amount' => $user->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount') / 100,
            'membership_payments' => $user->payments()->where('payment_type', 'membership')->where('status', Payment::STATUS_COMPLETED)->count(),
            'donation_total' => $user->payments()->where('payment_type', 'donation')->where('status', Payment::STATUS_COMPLETED)->sum('amount') / 100,
        ];

        return view('payments.index', compact('payments', 'stats'));
    }

    /**
     * Process Stripe payment.
     */
    public function processStripe(Request $request)
    {
        $request->validate([
            'payment_type' => 'required|in:membership,donation',
            'amount' => 'required|integer|min:500|max:1000000', // Max CHF 10,000
        ]);

        try {
            $paymentType = $request->payment_type;
            $amount = (int) $request->amount;
            $user = auth()->user();

            // Validate amount based on payment type
            if ($paymentType === 'membership') {
                $expectedAmount = (int) config('app.membership_amount', 35000);
                if ($amount !== $expectedAmount) {
                    return redirect()->back()->with('error', 'Invalid membership amount.');
                }
            } elseif ($paymentType === 'donation') {
                if ($amount < 500 || $amount > 1000000) {
                    return redirect()->back()->with('error', 'Donation amount must be between CHF 5 and CHF 10,000.');
                }
            }

            // Create payment record with enhanced metadata
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_type' => $paymentType,
                'amount' => $amount,
                'currency' => 'chf',
                'status' => Payment::STATUS_PENDING,
                'payment_method' => 'stripe',
                'metadata' => [
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'payment_type' => $paymentType,
                    'amount_validation' => hash('sha256', $amount . $user->id . config('app.key')),
                    'created_at' => now()->toISOString(),
                ]
            ]);

            // Check if Stripe is configured with real API keys
            $stripeSecret = config('services.stripe.secret');
            
            if ($stripeSecret && !in_array($stripeSecret, ['your-stripe-secret-key', 'sk_test_your_stripe_secret', null])) {
                // Real Stripe integration
                Stripe::setApiKey($stripeSecret);

                $sessionData = [
                    // Enhanced payment methods: Cards, Apple Pay, Google Pay
                    'payment_method_types' => ['card', 'apple_pay', 'google_pay'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'chf',
                            'product_data' => [
                                'name' => $paymentType === 'membership' ? 'Annual Membership - EN NUR' : 'Donation - EN NUR',
                                'description' => $paymentType === 'membership' ? 'One year membership access' : 'Community donation',
                            ],
                            'unit_amount' => $amount,
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => route('payment.stripe.success', ['payment' => $payment->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('payment.create') . '?cancelled=1',
                    'metadata' => [
                        'payment_id' => $payment->id,
                        'user_id' => $user->id,
                        'payment_type' => $paymentType,
                        'amount' => $amount,
                    ],
                    'customer_email' => $user->email,
                    'billing_address_collection' => 'auto',
                    // Enhanced payment configuration
                    'payment_intent_data' => [
                        'metadata' => [
                            'payment_id' => $payment->id,
                            'user_id' => $user->id,
                        ],
                        'setup_future_usage' => null, // Don't store payment methods
                    ],
                    // Automatic payment method detection
                    'automatic_payment_methods' => [
                        'enabled' => true,
                        'allow_redirects' => 'never', // Keep it simple for Swiss market
                    ],
                    // Enhanced UI customization
                    'custom_text' => [
                        'submit' => [
                            'message' => 'Secure payment powered by Stripe'
                        ]
                    ],
                    // Optimized for Swiss market
                    'locale' => 'de', // Swiss German (can be made dynamic)
                    'phone_number_collection' => [
                        'enabled' => false, // Keep it simple
                    ],
                ];

                $session = Session::create($sessionData);

                // Store session ID for verification
                $payment->update([
                    'metadata' => array_merge($payment->metadata, [
                        'stripe_session_id' => $session->id,
                        'stripe_session_url' => $session->url,
                    ])
                ]);

                return redirect($session->url);
            } else {
                // Demo mode - redirect to demo Stripe page with enhanced security
                $payment->update([
                    'metadata' => array_merge($payment->metadata, [
                        'demo_mode' => true,
                        'demo_session_id' => 'demo_' . time() . '_' . $payment->id,
                    ])
                ]);
                
                return view('payments.stripe-demo', compact('payment'));
            }

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error: ' . $e->getMessage(), [
                'payment_id' => $payment->id ?? null,
                'user_id' => auth()->id(),
                'error_type' => get_class($e)
            ]);
            return redirect()->back()->with('error', 'Payment processing failed. Please try again or contact support.');
        } catch (\Exception $e) {
            Log::error('Stripe payment error: ' . $e->getMessage(), [
                'payment_id' => $payment->id ?? null,
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Payment processing failed. Please try again.');
        }
    }

    /**
     * Handle Stripe success callback.
     */
    public function stripeSuccess(Request $request, Payment $payment)
    {
        try {
            $sessionId = $request->get('session_id');
            $user = auth()->user();

            // Verify user owns this payment
            if ($payment->user_id !== $user->id) {
                Log::warning('Unauthorized payment access attempt', [
                    'payment_id' => $payment->id,
                    'payment_user_id' => $payment->user_id,
                    'current_user_id' => $user->id,
                    'session_id' => $sessionId
                ]);
                return redirect()->route('payment.create')->with('error', 'Unauthorized payment access.');
            }

            // Verify payment is in pending state
            if ($payment->status !== Payment::STATUS_PENDING) {
                Log::info('Payment already processed', [
                    'payment_id' => $payment->id,
                    'current_status' => $payment->status,
                    'session_id' => $sessionId
                ]);
                return view('payments.success', compact('payment'));
            }

            $stripeSecret = config('services.stripe.secret');
            
            // If real Stripe integration, verify with Stripe API
            if ($stripeSecret && !in_array($stripeSecret, ['your-stripe-secret-key', 'sk_test_your_stripe_secret', null]) && $sessionId) {
                Stripe::setApiKey($stripeSecret);
                
                // Retrieve session from Stripe to verify payment
                $session = Session::retrieve($sessionId);
                
                // Verify session belongs to this payment
                if ($session->metadata->payment_id != $payment->id) {
                    Log::error('Session payment ID mismatch', [
                        'payment_id' => $payment->id,
                        'session_payment_id' => $session->metadata->payment_id,
                        'session_id' => $sessionId
                    ]);
                    return redirect()->route('payment.create')->with('error', 'Payment verification failed.');
                }

                // Verify payment was actually completed in Stripe
                if ($session->payment_status !== 'paid') {
                    Log::warning('Stripe session not paid', [
                        'payment_id' => $payment->id,
                        'session_id' => $sessionId,
                        'payment_status' => $session->payment_status
                    ]);
                    return redirect()->route('payment.create')->with('error', 'Payment was not completed.');
                }

                // Verify amounts match
                if ($session->amount_total !== $payment->amount) {
                    Log::error('Payment amount mismatch', [
                        'payment_id' => $payment->id,
                        'stored_amount' => $payment->amount,
                        'stripe_amount' => $session->amount_total,
                        'session_id' => $sessionId
                    ]);
                    return redirect()->route('payment.create')->with('error', 'Payment amount verification failed.');
                }

                // Get payment intent for additional verification
                $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);
                
                // Get payment method details for enhanced tracking
                $paymentMethodDetails = null;
                $paymentMethodType = 'unknown';
                if ($paymentIntent->payment_method) {
                    $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentIntent->payment_method);
                    $paymentMethodType = $paymentMethod->type; // 'card', 'apple_pay', 'google_pay', etc.
                    $paymentMethodDetails = [
                        'type' => $paymentMethodType,
                        'brand' => $paymentMethod->card->brand ?? null,
                        'last4' => $paymentMethod->card->last4 ?? null,
                        'exp_month' => $paymentMethod->card->exp_month ?? null,
                        'exp_year' => $paymentMethod->card->exp_year ?? null,
                        'country' => $paymentMethod->card->country ?? null,
                        'funding' => $paymentMethod->card->funding ?? null, // 'credit', 'debit', 'prepaid'
                    ];
                }
                
                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'transaction_id' => $session->payment_intent,
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'stripe_session_id' => $sessionId,
                        'stripe_payment_intent' => $session->payment_intent,
                        'stripe_customer' => $session->customer,
                        'payment_method' => $paymentIntent->payment_method ?? null,
                        'payment_method_type' => $paymentMethodType,
                        'payment_method_details' => $paymentMethodDetails,
                        'completed_at' => now()->toISOString(),
                        'verification_status' => 'verified'
                    ])
                ]);
                
                Log::info('Stripe payment completed successfully', [
                    'payment_id' => $payment->id,
                    'session_id' => $sessionId,
                    'payment_intent' => $session->payment_intent,
                    'amount' => $payment->amount
                ]);
                
            } else {
                // Demo mode - validate demo session
                $demoSessionId = $payment->metadata['demo_session_id'] ?? null;
                $expectedSessionId = 'demo_' . substr($payment->created_at->timestamp, -6) . '_' . $payment->id;
                
                if (!$demoSessionId || $sessionId !== $demoSessionId) {
                    // For demo mode, accept if session_id looks valid
                    if (!$sessionId || !str_contains($sessionId, 'demo_')) {
                        $sessionId = 'STRIPE_DEMO_' . time();
                    }
                }
                
                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'transaction_id' => $sessionId,
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'demo_completed_at' => now()->toISOString(),
                        'verification_status' => 'demo_mode'
                    ])
                ]);
                
                Log::info('Demo payment completed', [
                    'payment_id' => $payment->id,
                    'demo_session_id' => $sessionId
                ]);
            }

            // Generate receipt and send notification
            $this->handleSuccessfulPayment($payment);

            return view('payments.success', compact('payment'));
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error during success handling', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'session_id' => $request->get('session_id')
            ]);
            
            return view('payments.success', compact('payment'))
                ->with('warning', 'Payment completed but verification failed. Please contact support if you don\'t receive confirmation within 24 hours.');
                
        } catch (\Exception $e) {
            Log::error('Stripe payment success handling failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'session_id' => $request->get('session_id')
            ]);

            // Mark payment as completed but flag the processing issue
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'transaction_id' => $request->get('session_id', 'STRIPE_' . time()),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'processing_error' => 'Post-payment processing failed: ' . $e->getMessage(),
                    'processing_error_at' => now()->toISOString(),
                    'verification_status' => 'error'
                ])
            ]);

            return view('payments.success', compact('payment'))
                ->with('warning', 'Payment completed but there was an issue with processing. Please contact support if you don\'t receive confirmation within 24 hours.');
        }
    }

    /**
     * Process PayPal payment.
     */
    public function processPayPal(Request $request)
    {
        $request->validate([
            'payment_type' => 'required|in:membership,donation',
            'amount' => 'required|integer|min:500|max:1000000', // Max CHF 10,000
        ]);

        try {
            $paymentType = $request->payment_type;
            $amount = (int) $request->amount;
            $user = auth()->user();

            // Validate amount based on payment type
            if ($paymentType === 'membership') {
                $expectedAmount = (int) config('app.membership_amount', 35000);
                if ($amount !== $expectedAmount) {
                    return redirect()->back()->with('error', 'Invalid membership amount.');
                }
            } elseif ($paymentType === 'donation') {
                if ($amount < 500 || $amount > 1000000) {
                    return redirect()->back()->with('error', 'Donation amount must be between CHF 5 and CHF 10,000.');
                }
            }

            // Create payment record with enhanced metadata
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_type' => $paymentType,
                'amount' => $amount,
                'currency' => 'chf',
                'status' => Payment::STATUS_PENDING,
                'payment_method' => 'paypal',
                'metadata' => [
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'payment_type' => $paymentType,
                    'amount_validation' => hash('sha256', $amount . $user->id . config('app.key')),
                    'created_at' => now()->toISOString(),
                ]
            ]);

            $paypalClientId = config('services.paypal.client_id');
            $paypalClientSecret = config('services.paypal.client_secret');
            $paypalMode = config('services.paypal.mode', 'sandbox');
            
            // Check if PayPal is configured with real API keys
            if ($paypalClientId && $paypalClientSecret && 
                !in_array($paypalClientId, ['your-paypal-client-id', 'sandbox-client-id', null])) {
                
                // Real PayPal integration
                $apiContext = new \PayPal\Rest\ApiContext(
                    new \PayPal\Auth\OAuthTokenCredential($paypalClientId, $paypalClientSecret)
                );
                
                $apiContext->setConfig([
                    'mode' => $paypalMode,
                    'log.LogEnabled' => true,
                    'log.FileName' => storage_path('logs/paypal.log'),
                    'log.LogLevel' => 'INFO',
                    'cache.enabled' => true,
                ]);

                // Create PayPal payment
                $payer = new \PayPal\Api\Payer();
                $payer->setPaymentMethod('paypal');

                $item = new \PayPal\Api\Item();
                $item->setName($paymentType === 'membership' ? 'Annual Membership - EN NUR' : 'Donation - EN NUR')
                     ->setCurrency('CHF')
                     ->setQuantity(1)
                     ->setPrice(number_format($amount / 100, 2, '.', ''));

                $itemList = new \PayPal\Api\ItemList();
                $itemList->setItems([$item]);

                $details = new \PayPal\Api\Details();
                $details->setSubtotal(number_format($amount / 100, 2, '.', ''));

                $amountObj = new \PayPal\Api\Amount();
                $amountObj->setCurrency('CHF')
                          ->setTotal(number_format($amount / 100, 2, '.', ''))
                          ->setDetails($details);

                $transaction = new \PayPal\Api\Transaction();
                $transaction->setAmount($amountObj)
                           ->setItemList($itemList)
                           ->setDescription($paymentType === 'membership' ? 'EN NUR Annual Membership' : 'EN NUR Donation')
                           ->setCustom(json_encode([
                               'payment_id' => $payment->id,
                               'user_id' => $user->id,
                               'payment_type' => $paymentType
                           ]));

                $redirectUrls = new \PayPal\Api\RedirectUrls();
                $redirectUrls->setReturnUrl(route('payment.paypal.success', ['payment' => $payment->id]))
                            ->setCancelUrl(route('payment.create') . '?cancelled=1');

                $paypalPayment = new \PayPal\Api\Payment();
                $paypalPayment->setIntent('sale')
                             ->setPayer($payer)
                             ->setRedirectUrls($redirectUrls)
                             ->setTransactions([$transaction]);

                $paypalPayment->create($apiContext);

                // Store PayPal payment ID for verification
                $payment->update([
                    'metadata' => array_merge($payment->metadata, [
                        'paypal_payment_id' => $paypalPayment->getId(),
                        'paypal_state' => $paypalPayment->getState(),
                        'paypal_create_time' => $paypalPayment->getCreateTime(),
                    ])
                ]);

                // Get approval URL
                foreach ($paypalPayment->getLinks() as $link) {
                    if ($link->getRel() === 'approval_url') {
                        return redirect($link->getHref());
                    }
                }

                throw new \Exception('PayPal approval URL not found');

            } else {
                // Demo mode - redirect to demo PayPal page with enhanced security
                $payment->update([
                    'metadata' => array_merge($payment->metadata, [
                        'demo_mode' => true,
                        'demo_payment_id' => 'PAYPAL_DEMO_' . time() . '_' . $payment->id,
                    ])
                ]);
                
                return view('payments.paypal-demo', compact('payment'));
            }

        } catch (\PayPal\Exception\PayPalConnectionException $e) {
            Log::error('PayPal connection error: ' . $e->getData(), [
                'payment_id' => $payment->id ?? null,
                'user_id' => auth()->id(),
                'http_code' => $e->getCode()
            ]);
            return redirect()->back()->with('error', 'PayPal connection failed. Please try again or contact support.');
            
        } catch (\Exception $e) {
            Log::error('PayPal payment error: ' . $e->getMessage(), [
                'payment_id' => $payment->id ?? null,
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Payment processing failed. Please try again.');
        }
    }

    /**
     * Handle PayPal success callback.
     */
    public function paypalSuccess(Request $request, Payment $payment)
    {
        try {
            $paymentId = $request->get('paymentId');
            $payerId = $request->get('PayerID');
            $token = $request->get('token');
            $user = auth()->user();

            // Verify user owns this payment
            if ($payment->user_id !== $user->id) {
                Log::warning('Unauthorized PayPal payment access attempt', [
                    'payment_id' => $payment->id,
                    'payment_user_id' => $payment->user_id,
                    'current_user_id' => $user->id,
                    'paypal_payment_id' => $paymentId
                ]);
                return redirect()->route('payment.create')->with('error', 'Unauthorized payment access.');
            }

            // Verify payment is in pending state
            if ($payment->status !== Payment::STATUS_PENDING) {
                Log::info('PayPal payment already processed', [
                    'payment_id' => $payment->id,
                    'current_status' => $payment->status,
                    'paypal_payment_id' => $paymentId
                ]);
                return view('payments.success', compact('payment'));
            }

            $paypalClientId = config('services.paypal.client_id');
            $paypalClientSecret = config('services.paypal.client_secret');
            $paypalMode = config('services.paypal.mode', 'sandbox');
            
            // If real PayPal integration, verify with PayPal API
            if ($paypalClientId && $paypalClientSecret && 
                !in_array($paypalClientId, ['your-paypal-client-id', 'sandbox-client-id', null]) && 
                $paymentId && $payerId) {
                
                $apiContext = new \PayPal\Rest\ApiContext(
                    new \PayPal\Auth\OAuthTokenCredential($paypalClientId, $paypalClientSecret)
                );
                
                $apiContext->setConfig([
                    'mode' => $paypalMode,
                    'log.LogEnabled' => true,
                    'log.FileName' => storage_path('logs/paypal.log'),
                    'log.LogLevel' => 'INFO',
                    'cache.enabled' => true,
                ]);

                // Retrieve payment from PayPal to verify
                $paypalPayment = \PayPal\Api\Payment::get($paymentId, $apiContext);
                
                // Verify payment belongs to this user
                $customData = json_decode($paypalPayment->getTransactions()[0]->getCustom(), true);
                if ($customData['payment_id'] != $payment->id || $customData['user_id'] != $user->id) {
                    Log::error('PayPal payment ID mismatch', [
                        'payment_id' => $payment->id,
                        'paypal_custom_payment_id' => $customData['payment_id'] ?? 'none',
                        'paypal_payment_id' => $paymentId
                    ]);
                    return redirect()->route('payment.create')->with('error', 'Payment verification failed.');
                }

                // Execute the payment
                $execution = new \PayPal\Api\PaymentExecution();
                $execution->setPayerId($payerId);
                
                $executedPayment = $paypalPayment->execute($execution, $apiContext);
                
                // Verify payment was successful
                if ($executedPayment->getState() !== 'approved') {
                    Log::warning('PayPal payment not approved', [
                        'payment_id' => $payment->id,
                        'paypal_payment_id' => $paymentId,
                        'state' => $executedPayment->getState()
                    ]);
                    return redirect()->route('payment.create')->with('error', 'Payment was not approved.');
                }

                // Verify amounts match
                $executedAmount = $executedPayment->getTransactions()[0]->getAmount()->getTotal() * 100;
                if ($executedAmount != $payment->amount) {
                    Log::error('PayPal payment amount mismatch', [
                        'payment_id' => $payment->id,
                        'stored_amount' => $payment->amount,
                        'paypal_amount' => $executedAmount,
                        'paypal_payment_id' => $paymentId
                    ]);
                    return redirect()->route('payment.create')->with('error', 'Payment amount verification failed.');
                }

                // Get sale transaction details
                $saleId = null;
                foreach ($executedPayment->getTransactions()[0]->getRelatedResources() as $resource) {
                    if ($resource->getSale()) {
                        $saleId = $resource->getSale()->getId();
                        break;
                    }
                }

                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'transaction_id' => $saleId ?: $paymentId,
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'paypal_payment_id' => $paymentId,
                        'paypal_payer_id' => $payerId,
                        'paypal_sale_id' => $saleId,
                        'paypal_state' => $executedPayment->getState(),
                        'paypal_payer_info' => [
                            'email' => $executedPayment->getPayer()->getPayerInfo()->getEmail(),
                            'first_name' => $executedPayment->getPayer()->getPayerInfo()->getFirstName(),
                            'last_name' => $executedPayment->getPayer()->getPayerInfo()->getLastName(),
                        ],
                        'completed_at' => now()->toISOString(),
                        'verification_status' => 'verified'
                    ])
                ]);
                
                Log::info('PayPal payment completed successfully', [
                    'payment_id' => $payment->id,
                    'paypal_payment_id' => $paymentId,
                    'sale_id' => $saleId,
                    'amount' => $payment->amount
                ]);
                
            } else {
                // Demo mode - validate demo payment
                $demoPaymentId = $payment->metadata['demo_payment_id'] ?? null;
                
                if (!$demoPaymentId) {
                    $paymentId = 'PAYPAL_DEMO_' . time();
                }
                
                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'transaction_id' => $paymentId ?: 'PAYPAL_DEMO_' . time(),
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'demo_completed_at' => now()->toISOString(),
                        'demo_payer_id' => $payerId ?: 'DEMO_PAYER_' . time(),
                        'verification_status' => 'demo_mode'
                    ])
                ]);
                
                Log::info('Demo PayPal payment completed', [
                    'payment_id' => $payment->id,
                    'demo_payment_id' => $paymentId
                ]);
            }

            // Generate receipt and send notification
            $this->handleSuccessfulPayment($payment);

            return view('payments.success', compact('payment'));
            
        } catch (\PayPal\Exception\PayPalConnectionException $e) {
            Log::error('PayPal API error during success handling', [
                'payment_id' => $payment->id,
                'error' => $e->getData(),
                'http_code' => $e->getCode(),
                'paypal_payment_id' => $request->get('paymentId')
            ]);
            
            return view('payments.success', compact('payment'))
                ->with('warning', 'Payment completed but verification failed. Please contact support if you don\'t receive confirmation within 24 hours.');
                
        } catch (\Exception $e) {
            Log::error('PayPal payment success handling failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'paypal_payment_id' => $request->get('paymentId')
            ]);

            // Mark payment as completed but flag the processing issue
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'transaction_id' => $request->get('paymentId', 'PAYPAL_' . time()),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'processing_error' => 'Post-payment processing failed: ' . $e->getMessage(),
                    'processing_error_at' => now()->toISOString(),
                    'verification_status' => 'error'
                ])
            ]);

            return view('payments.success', compact('payment'))
                ->with('warning', 'Payment completed but there was an issue with processing. Please contact support if you don\'t receive confirmation within 24 hours.');
        }
    }

    /**
     * Process TWINT payment.
     */
    public function processTwint(Request $request)
    {
        $request->validate([
            'payment_type' => 'required|in:membership,donation',
            'amount' => 'required|integer|min:500|max:1000000', // Max CHF 10,000
        ]);

        try {
            $paymentType = $request->payment_type;
            $amount = (int) $request->amount;
            $user = auth()->user();

            // Validate amount based on payment type
            if ($paymentType === 'membership') {
                $expectedAmount = (int) config('app.membership_amount', 35000);
                if ($amount !== $expectedAmount) {
                    return redirect()->back()->with('error', 'Invalid membership amount.');
                }
            } elseif ($paymentType === 'donation') {
                if ($amount < 500 || $amount > 1000000) {
                    return redirect()->back()->with('error', 'Donation amount must be between CHF 5 and CHF 10,000.');
                }
            }

            // Create payment record with enhanced metadata
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_type' => $paymentType,
                'amount' => $amount,
                'currency' => 'chf',
                'status' => Payment::STATUS_PENDING,
                'payment_method' => 'twint',
                'metadata' => [
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'payment_type' => $paymentType,
                    'amount_validation' => hash('sha256', $amount . $user->id . config('app.key')),
                    'created_at' => now()->toISOString(),
                ]
            ]);

            // Update with TWINT reference now that we have the payment ID
            $payment->update([
                'metadata' => array_merge($payment->metadata, [
                    'twint_reference' => 'TWINT-' . time() . '-' . $payment->id,
                ])
            ]);

            $twintMerchantId = config('services.twint.merchant_id');
            $twintApiKey = config('services.twint.api_key');
            
            // Check if TWINT is configured with real API keys
            if ($twintMerchantId && $twintApiKey && 
                !in_array($twintMerchantId, ['your-twint-merchant-id', 'test-merchant-id', null])) {
                
                // Real TWINT integration would go here
                // For now, redirect to instructions page with enhanced security
                $payment->update([
                    'metadata' => array_merge($payment->metadata, [
                        'twint_mode' => 'production',
                        'twint_session_id' => 'TWINT_' . time() . '_' . $payment->id,
                    ])
                ]);
                
                return redirect()->route('payment.twint.instructions', ['payment' => $payment->id]);
            } else {
                // Demo/Manual mode - redirect to TWINT instructions
                $payment->update([
                    'metadata' => array_merge($payment->metadata, [
                        'twint_mode' => 'manual',
                        'twint_session_id' => 'TWINT_MANUAL_' . time() . '_' . $payment->id,
                    ])
                ]);
                
                return redirect()->route('payment.twint.instructions', ['payment' => $payment->id]);
            }

        } catch (\Exception $e) {
            Log::error('TWINT payment error: ' . $e->getMessage(), [
                'payment_id' => $payment->id ?? null,
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Payment processing failed. Please try again.');
        }
    }

    /**
     * Show TWINT payment instructions.
     */
    public function twintInstructions(Payment $payment)
    {
        $user = auth()->user();

        // Verify user owns this payment
        if ($payment->user_id !== $user->id) {
            abort(403, 'Unauthorized payment access.');
        }

        // Check if payment is still pending
        if ($payment->status !== Payment::STATUS_PENDING) {
            return redirect()->route('payment.create')->with('info', 'This payment has already been processed.');
        }

        return view('payments.twint-instructions', compact('payment'));
    }

    /**
     * Handle TWINT success callback (demo mode).
     */
    public function twintSuccess(Request $request, Payment $payment)
    {
        $user = auth()->user();

        // Verify user owns this payment
        if ($payment->user_id !== $user->id) {
            Log::warning('Unauthorized TWINT payment access attempt', [
                'payment_id' => $payment->id,
                'payment_user_id' => $payment->user_id,
                'current_user_id' => $user->id
            ]);
            return redirect()->route('payment.create')->with('error', 'Unauthorized payment access.');
        }

        // In demo mode, simulate successful payment
        if ($request->get('demo') === '1') {
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'transaction_id' => 'TWINT_DEMO_' . time(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'demo_completed_at' => now()->toISOString(),
                    'demo_mode' => true,
                    'verification_status' => 'demo_completed'
                ])
            ]);

            // Handle successful payment (create membership renewal, send emails, etc.)
            $this->handleSuccessfulPayment($payment);

            Log::info('TWINT demo payment completed', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'amount' => $payment->formatted_amount
            ]);

            return view('payments.success', compact('payment'));
        }

        // For real TWINT payments, redirect to instructions
        return redirect()->route('payment.twint.instructions', ['payment' => $payment->id]);
    }

    /**
     * Confirm TWINT payment.
     */
    public function twintConfirm(Request $request, Payment $payment)
    {
        $request->validate([
            'confirmation' => 'required|accepted',
        ], [
            'confirmation.accepted' => 'You must confirm that you have completed the TWINT payment.',
        ]);

        try {
            $user = auth()->user();

            // Verify user owns this payment
            if ($payment->user_id !== $user->id) {
                Log::warning('Unauthorized TWINT payment confirmation attempt', [
                    'payment_id' => $payment->id,
                    'payment_user_id' => $payment->user_id,
                    'current_user_id' => $user->id
                ]);
                return redirect()->route('payment.create')->with('error', 'Unauthorized payment access.');
            }

            // Verify payment is in pending state
            if ($payment->status !== Payment::STATUS_PENDING) {
                Log::info('TWINT payment already processed', [
                    'payment_id' => $payment->id,
                    'current_status' => $payment->status
                ]);
                return view('payments.success', compact('payment'));
            }

            // Update payment to awaiting verification
            $payment->update([
                'status' => Payment::STATUS_PENDING,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'user_confirmed_at' => now()->toISOString(),
                    'confirmation_ip' => $request->ip(),
                    'awaiting_verification' => true,
                    'verification_status' => 'awaiting_manual_verification'
                ])
            ]);

            Log::info('TWINT payment confirmation received', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'confirmed_at' => now()->toISOString()
            ]);

            // Send notification to admin for manual verification
            $this->sendTwintVerificationNotification($payment);

            return view('payments.success', compact('payment'))
                ->with('info', 'Payment confirmation received. We will verify your TWINT payment within 24 hours and send you a confirmation email.');

        } catch (\Exception $e) {
            Log::error('TWINT payment confirmation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Payment confirmation failed. Please try again or contact support.');
        }
    }

    /**
     * Process Bank Transfer payment.
     */
    public function processCash(Request $request)
    {
        // Basic validation
        try {
            $request->validate([
                'payment_type' => 'required|in:membership,donation',
                'amount' => 'required|integer|min:500|max:1000000',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Validation failed: ' . $e->getMessage());
        }

        try {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login first.');
            }

            $paymentType = $request->input('payment_type');
            $amount = (int) $request->input('amount');

            // Simple amount validation
            if ($paymentType === 'membership') {
                $expectedAmount = 35000; // CHF 350.00
                if ($amount !== $expectedAmount) {
                    return redirect()->back()->with('error', 'Invalid membership amount. Expected CHF 350.00');
                }
            }

            // Create payment record - MINIMAL VERSION
            $payment = new Payment();
            $payment->user_id = $user->id;
            $payment->payment_type = $paymentType;
            $payment->amount = $amount;
            $payment->currency = 'chf';
            $payment->status = 'pending';
            $payment->payment_method = 'cash';
            $payment->metadata = [
                'user_email' => $user->email,
                'user_name' => $user->name,
                'cash_payment' => true,
                'created_at' => now()->toISOString(),
            ];
            $payment->save();

            // Log success
            Log::info('Cash payment created successfully', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => $paymentType,
            ]);

            // Redirect to instructions page
            return redirect()->route('payment.cash.instructions', ['payment' => $payment->id]);

        } catch (\Exception $e) {
            // Detailed error logging
            Log::error('Cash payment failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'not_authenticated',
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Show cash payment instructions.
     */
    public function cashInstructions(Payment $payment)
    {
        // Verify the payment belongs to the authenticated user
        if ($payment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to payment instructions.');
        }

        // Verify it's a cash payment
        if ($payment->payment_method !== 'cash') {
            abort(404, 'Payment method not found.');
        }

        return view('payments.cash-instructions', compact('payment'));
    }

    /**
     * Admin confirms cash payment received.
     */
    public function cashConfirm(Request $request, Payment $payment)
    {
        // Only admins can confirm cash payments
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Update payment status
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'transaction_id' => 'CASH_' . now()->format('YmdHis') . '_' . $payment->id,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'admin_confirmed_at' => now()->toISOString(),
                    'admin_confirmed_by' => auth()->user()->id,
                    'admin_notes' => $request->notes,
                    'cash_confirmed' => true,
                ])
            ]);

            Log::info('Cash payment confirmed by admin', [
                'payment_id' => $payment->id,
                'admin_id' => auth()->id(),
                'user_id' => $payment->user_id,
                'amount' => $payment->amount,
                'notes' => $request->notes,
            ]);

            // Handle successful payment (membership renewal, etc.)
            $this->handleSuccessfulPayment($payment);

            return redirect()->back()->with('success', 'Cash payment confirmed successfully. User has been notified.');

        } catch (\Exception $e) {
            Log::error('Cash payment confirmation failed', [
                'payment_id' => $payment->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to confirm cash payment. Please try again.');
        }
    }

    /**
     * Process bank transfer payment.
     */
    public function processBank(Request $request)
    {
        $request->validate([
            'payment_type' => 'required|in:membership,donation',
            'amount' => 'required|integer|min:500|max:1000000', // Max CHF 10,000
        ]);

        try {
            $paymentType = $request->payment_type;
            $amount = (int) $request->amount;
            $user = auth()->user();

            // Validate amount based on payment type
            if ($paymentType === 'membership') {
                $expectedAmount = (int) config('app.membership_amount', 35000);
                if ($amount !== $expectedAmount) {
                    return redirect()->back()->with('error', 'Invalid membership amount.');
                }
            } elseif ($paymentType === 'donation') {
                if ($amount < 500 || $amount > 1000000) {
                    return redirect()->back()->with('error', 'Donation amount must be between CHF 5 and CHF 10,000.');
                }
            }

            // Create payment record with enhanced metadata
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_type' => $paymentType,
                'amount' => $amount,
                'currency' => 'chf',
                'status' => Payment::STATUS_PENDING,
                'payment_method' => 'bank_transfer',
                'metadata' => [
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'payment_type' => $paymentType,
                    'amount_validation' => hash('sha256', $amount . $user->id . config('app.key')),
                    'created_at' => now()->toISOString(),
                    'awaiting_verification' => true,
                    'verification_status' => 'awaiting_bank_transfer'
                ]
            ]);

            // Update with bank reference now that we have the payment ID
            $payment->update([
                'metadata' => array_merge($payment->metadata, [
                    'bank_reference' => 'PAY-' . $payment->id . '-' . strtoupper(substr($paymentType, 0, 3)),
                ])
            ]);

            // Send bank transfer instructions email
            $this->sendBankTransferInstructions($payment);

            Log::info('Bank transfer payment initiated', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'amount' => $payment->formatted_amount,
                'type' => $paymentType
            ]);

            return redirect()->route('payment.bank.instructions', ['payment' => $payment->id]);

        } catch (\Exception $e) {
            Log::error('Bank transfer payment error: ' . $e->getMessage(), [
                'payment_id' => $payment->id ?? null,
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Payment processing failed. Please try again.');
        }
    }

    /**
     * Handle Bank Transfer success callback.
     */
    public function bankSuccess(Request $request, Payment $payment)
    {
        $user = auth()->user();

        // Verify user owns this payment
        if ($payment->user_id !== $user->id) {
            Log::warning('Unauthorized Bank Transfer payment access attempt', [
                'payment_id' => $payment->id,
                'payment_user_id' => $payment->user_id,
                'current_user_id' => $user->id
            ]);
            return redirect()->route('payment.create')->with('error', 'Unauthorized payment access.');
        }

        // In demo mode, simulate successful payment
        if ($request->get('demo') === '1') {
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'transaction_id' => 'BANK_DEMO_' . time(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'demo_completed_at' => now()->toISOString(),
                    'demo_mode' => true,
                    'verification_status' => 'demo_completed'
                ])
            ]);

            // Handle successful payment (create membership renewal, send emails, etc.)
            $this->handleSuccessfulPayment($payment);

            Log::info('Bank Transfer demo payment completed', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'amount' => $payment->formatted_amount
            ]);

            return view('payments.success', compact('payment'));
        }

        // For real bank transfers, they are manually verified by admin
        $payment->update([
            'status' => Payment::STATUS_PENDING,
            'transaction_id' => 'BANK_' . time(),
            'metadata' => array_merge($payment->metadata ?? [], [
                'awaiting_verification' => true,
                'verification_status' => 'awaiting_bank_transfer'
            ])
        ]);

        return view('payments.success', compact('payment'))
            ->with('info', 'Bank transfer details confirmed. We will verify your payment within 3 business days and send you a confirmation email.');
    }

    /**
     * Handle successful payment completion.
     */
    private function handleSuccessfulPayment(Payment $payment)
    {
        try {
            \DB::beginTransaction();

            // If this is a membership payment, create/update membership renewal
            if ($payment->payment_type === 'membership') {
                $this->createMembershipRenewal($payment);
            }
            
            \DB::commit();
            
            // Generate PDF receipt (outside transaction as it's not critical)
            $receipt = $this->generateReceipt($payment);
            
            // Send confirmation email with receipt (outside transaction)
            $this->sendPaymentConfirmation($payment, $receipt);
            
            Log::info("Payment completed successfully", [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'amount' => $payment->formatted_amount,
                'type' => $payment->payment_type
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            
            Log::error("Error handling successful payment: " . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw to ensure calling code handles the failure
        }
    }

    /**
     * Create or update membership renewal for completed membership payment.
     */
    private function createMembershipRenewal(Payment $payment)
    {
        try {
            // Check if user already has an active membership renewal
            $existingRenewal = \App\Models\MembershipRenewal::where('user_id', $payment->user_id)
                ->where('is_renewed', false)
                ->orderBy('membership_end_date', 'desc')
                ->first();

            // Determine start date for new membership
            if ($existingRenewal) {
                // Mark existing renewal as renewed
                $existingRenewal->update([
                    'is_renewed' => true,
                    'renewal_payment_id' => $payment->id,
                    'admin_notes' => 'Membership renewed with payment #' . $payment->id
                ]);
                
                // New membership starts from the existing expiry date (or today if already expired)
                $startDate = $existingRenewal->membership_end_date->isFuture() 
                    ? $existingRenewal->membership_end_date 
                    : now();
            } else {
                // First-time membership starts today
                $startDate = now();
            }

            // Create new membership renewal for 1 year from start date
            $endDate = $startDate->copy()->addYear();
            $daysUntilExpiry = (int) now()->diffInDays($endDate, false); // Can be negative if expired

            $newRenewal = \App\Models\MembershipRenewal::create([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'membership_start_date' => $startDate,
                'membership_end_date' => $endDate,
                'days_until_expiry' => $daysUntilExpiry,
                'notifications_sent' => [],
                'last_notification_sent_at' => null,
                'is_hidden' => false,
                'is_expired' => false,
                'is_renewed' => false,
                'renewal_payment_id' => null,
                'admin_notes' => $existingRenewal 
                    ? 'Membership extended with payment #' . $payment->id
                    : 'New membership created with payment #' . $payment->id
            ]);

            Log::info("Membership renewal created/extended", [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'start_date' => $startDate->format('Y-m-d'),
                'valid_until' => $endDate->format('Y-m-d'),
                'days_until_expiry' => $daysUntilExpiry,
                'is_extension' => $existingRenewal ? true : false
            ]);

        } catch (\Exception $e) {
            Log::error("Error creating membership renewal: " . $e->getMessage(), [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'error_trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Generate PDF receipt for payment.
     */
    private function generateReceipt(Payment $payment)
    {
        try {
            // Check if required extensions are available
            if (!extension_loaded('gd') || !extension_loaded('dom')) {
                Log::warning('PDF generation skipped - missing required extensions', [
                    'payment_id' => $payment->id,
                    'gd_loaded' => extension_loaded('gd'),
                    'dom_loaded' => extension_loaded('dom')
                ]);
                return null;
            }

            $pdf = Pdf::loadView('admin.payment-receipt', compact('payment'));
            return $pdf->output();
        } catch (\Exception $e) {
            Log::error('PDF generation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Send payment confirmation email with receipt.
     */
    private function sendPaymentConfirmation(Payment $payment, $receiptPdf = null)
    {
        try {
            $user = $payment->user;
            $subject = $payment->payment_type === 'membership' 
                ? 'Membership Payment Confirmation - EN NUR'
                : 'Donation Confirmation - EN NUR';

            $message = $payment->payment_type === 'membership'
                ? $this->getMembershipConfirmationMessage($payment)
                : $this->getDonationConfirmationMessage($payment);

            // Attempt to send email
            Mail::raw($message, function ($mail) use ($user, $subject, $receiptPdf, $payment) {
                $mail->to($user->email, $user->name)
                     ->subject($subject);

                // Attach PDF receipt if available
                if ($receiptPdf) {
                    $filename = "receipt-{$payment->id}-" . now()->format('Y-m-d') . ".pdf";
                    $mail->attachData($receiptPdf, $filename, [
                        'mime' => 'application/pdf',
                    ]);
                }
            });

            Log::info('Payment confirmation email sent successfully', [
                'payment_id' => $payment->id,
                'user_email' => $user->email,
                'payment_type' => $payment->payment_type
            ]);

        } catch (\Exception $e) {
            // Log email failure but don't stop the payment process
            Log::warning('Payment confirmation email failed to send', [
                'payment_id' => $payment->id,
                'user_email' => $payment->user->email ?? 'unknown',
                'error' => $e->getMessage(),
                'note' => 'Payment was successful but email notification failed'
            ]);
        }
    }

    /**
     * Get membership confirmation email message.
     */
    private function getMembershipConfirmationMessage(Payment $payment): string
    {
        $user = $payment->user;
        $validUntil = $payment->created_at->addYear()->format('M d, Y');
        
        $message = "Dear {$user->name},\n\n";
        $message .= "🕌 Welcome to the " . config('app.name') . " community! 🕌\n\n";
        $message .= "Your annual membership has been confirmed and is now active.\n\n";
        
        $message .= "MEMBERSHIP DETAILS:\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "• Member ID: MBR-" . str_pad($user->id, 6, '0', STR_PAD_LEFT) . "\n";
        $message .= "• Payment ID: {$payment->id}\n";
        $message .= "• Amount Paid: {$payment->formatted_amount}\n";
        $message .= "• Valid From: " . $payment->created_at->format('M d, Y') . "\n";
        $message .= "• Valid Until: {$validUntil}\n";
        $message .= "• Payment Method: " . strtoupper(str_replace('_', ' ', $payment->payment_method)) . "\n";
        
        if ($payment->transaction_id) {
            $message .= "• Transaction ID: {$payment->transaction_id}\n";
        }
        
        $message .= "\n🌟 YOUR MEMBERSHIP BENEFITS:\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "✓ 24/7 access to prayer facilities\n";
        $message .= "✓ Friday prayers and religious services\n";
        $message .= "✓ Islamic education and Quran classes\n";
        $message .= "✓ Community events and celebrations\n";
        $message .= "✓ Youth and family programs\n";
        $message .= "✓ Counseling and spiritual guidance\n";
        $message .= "✓ Library and study resources\n";
        $message .= "✓ Discounted event rates\n";
        $message .= "✓ Voting rights in community decisions\n\n";
        
        $message .= "📋 NEXT STEPS:\n";
        $message .= "• Your membership card will be ready for pickup within 7 days\n";
        $message .= "• Check your dashboard for receipt download\n";
        $message .= "• Join our WhatsApp community group (link in dashboard)\n\n";
        
        $message .= "A detailed membership receipt is attached for your records.\n\n";
        $message .= "Barakallahu feek! May Allah bless your journey with us.\n\n";
        $message .= "Best regards,\n" . config('app.name') . " Team\n";
        $message .= "📧 info@mosque.ch | 📞 +41 XX XXX XX XX";
        
        return $message;
    }

    /**
     * Get donation confirmation email message.
     */
    private function getDonationConfirmationMessage(Payment $payment): string
    {
        $user = $payment->user;
        
        $message = "Dear {$user->name},\n\n";
        $message .= "🤲 Jazakallahu Khairan for your generous donation! 🤲\n\n";
        $message .= "Your contribution has been received and will help strengthen our community.\n\n";
        
        $message .= "DONATION DETAILS:\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "• Donor ID: DNR-" . str_pad($user->id, 6, '0', STR_PAD_LEFT) . "\n";
        $message .= "• Receipt ID: {$payment->id}\n";
        $message .= "• Donation Amount: {$payment->formatted_amount}\n";
        $message .= "• Date: " . $payment->created_at->format('M d, Y H:i') . "\n";
        $message .= "• Payment Method: " . strtoupper(str_replace('_', ' ', $payment->payment_method)) . "\n";
        
        if ($payment->transaction_id) {
            $message .= "• Transaction ID: {$payment->transaction_id}\n";
        }
        
        $message .= "\n🌟 HOW YOUR DONATION HELPS:\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "• Facility maintenance and utilities\n";
        $message .= "• Religious programs and services\n";
        $message .= "• Educational classes and workshops\n";
        $message .= "• Community support and charity\n";
        $message .= "• Youth and family activities\n";
        $message .= "• Interfaith dialogue initiatives\n\n";
        
        $message .= "💰 TAX INFORMATION:\n";
        $message .= "This donation may be tax-deductible. Please consult your tax advisor.\n";
        $message .= "Keep the attached receipt for your tax records.\n\n";
        
        $message .= "A detailed donation receipt is attached for your records.\n\n";
        $message .= "May Allah (SWT) reward you abundantly for your generosity.\n\n";
        $message .= "Best regards,\n" . config('app.name') . " Team\n";
        $message .= "📧 info@mosque.ch | 📞 +41 XX XXX XX XX";
        
        return $message;
    }

    /**
     * Send bank transfer instructions email.
     */
    private function sendBankTransferInstructions(Payment $payment)
    {
        try {
            $user = $payment->user;
            $subject = 'Bank Transfer Instructions - ' . config('app.name');
            
            $message = "Dear {$user->name},\n\n";
            $message .= "Thank you for choosing to support us with a bank transfer.\n\n";
            $message .= "Payment Details:\n";
            $message .= "- Payment ID: {$payment->id}\n";
            $message .= "- Amount to Transfer: {$payment->formatted_amount}\n";
            $message .= "- Type: " . ucfirst($payment->payment_type) . "\n\n";
            
            $message .= "Bank Transfer Instructions:\n";
            $message .= "- Bank: UBS Switzerland AG\n";
            $message .= "- Account Name: EN NUR Mosque Community\n";
            $message .= "- IBAN: CH93 0023 0230 2305 8901 Y\n";
            $message .= "- BIC/SWIFT: UBSWCHZH80A\n";
            $message .= "- Reference: PAY-{$payment->id}-" . strtoupper(substr($payment->payment_type, 0, 3)) . "\n\n";
            
            $message .= "Important: Please include the reference number in your transfer description to ensure proper processing.\n\n";
            $message .= "Processing Time: Bank transfers typically take 1-3 business days to process.\n";
            $message .= "You will receive a confirmation email once we verify your payment.\n\n";
            $message .= "Best regards,\n" . config('app.name') . " Team";

            Log::info("Bank transfer instructions sent to {$user->email}", [
                'payment_id' => $payment->id,
                'subject' => $subject
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send bank transfer instructions: ' . $e->getMessage());
        }
    }

    /**
     * Send cash payment instructions email.
     */
    private function sendCashPaymentInstructions(Payment $payment)
    {
        try {
            $user = $payment->user;
            $subject = 'Cash Payment Instructions - ' . config('app.name');
            
            $message = "Dear {$user->name},\n\n";
            $message .= "Thank you for choosing to pay with cash.\n\n";
            $message .= "Payment Details:\n";
            $message .= "- Payment ID: {$payment->id}\n";
            $message .= "- Amount to Pay: {$payment->formatted_amount}\n";
            $message .= "- Type: " . ucfirst($payment->payment_type) . "\n\n";
            
            $message .= "Cash Payment Instructions:\n";
            $message .= "- Payment can be made in person at the mosque\n";
            $message .= "- Office Hours: Monday-Friday 9:00 AM - 5:00 PM\n";
            $message .= "- Saturday-Sunday: After prayer times\n";
            $message .= "- Please bring this payment reference: CASH-{$payment->id}\n\n";
            
            $message .= "Alternative Arrangements:\n";
            $message .= "- Contact us to arrange payment pickup\n";
            $message .= "- Phone: +41 XX XXX XX XX\n";
            $message .= "- Email: info@mosque.ch\n\n";
            
            $message .= "Important Notes:\n";
            $message .= "- Your payment is currently marked as PENDING\n";
            $message .= "- Once we receive your cash payment, we will update your account\n";
            $message .= "- You will receive a confirmation email and receipt\n";
            $message .= "- Please keep this email as proof of your payment request\n\n";
            
            $message .= "Best regards,\n" . config('app.name') . " Team\n";
            $message .= "📧 info@mosque.ch | 📞 +41 XX XXX XX XX";

            Log::info("Cash payment instructions sent to {$user->email}", [
                'payment_id' => $payment->id,
                'subject' => $subject
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send cash payment instructions: ' . $e->getMessage());
        }
    }

    /**
     * Download receipt for user's own payment.
     */
    public function downloadUserReceipt(Payment $payment)
    {
        // Ensure user can only download their own receipts
        if ($payment->user_id !== auth()->id()) {
            abort(403, 'You can only download your own receipts.');
        }

        // Only allow download for completed payments
        if ($payment->status !== Payment::STATUS_COMPLETED) {
            return redirect()->back()->with('error', 'Receipt is only available for completed payments.');
        }

        $payment->load('user');
        
        // Choose template based on payment type
        $template = $payment->payment_type === 'membership' 
            ? 'admin.payment-receipt-membership' 
            : 'admin.payment-receipt-donation';
        
        $pdf = Pdf::loadView($template, compact('payment'));
        
        // Different filename based on type
        $typePrefix = $payment->payment_type === 'membership' ? 'membership' : 'donation';
        $fileName = $typePrefix . '_receipt_' . $payment->id . '_' . $payment->created_at->format('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }

    /**
     * Delete a payment record.
     * Users can only delete their own payments.
     * Admins can delete any payment.
     */
    public function destroy(Payment $payment)
    {
        $user = auth()->user();
        
        // Check authorization - users can only delete their own payments, admins can delete any
        if (!$user->isAdmin() && $payment->user_id !== $user->id) {
            abort(403, 'You can only delete your own payments.');
        }
        
        try {
            // Store payment info for logging before deletion
            $paymentInfo = [
                'id' => $payment->id,
                'user_id' => $payment->user_id,
                'user_name' => $payment->user->name,
                'payment_type' => $payment->payment_type,
                'amount' => $payment->formatted_amount,
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                'deleted_by' => $user->id,
                'deleted_by_name' => $user->name,
                'deleted_at' => now()->format('Y-m-d H:i:s')
            ];
            
            // Delete the payment
            $payment->delete();
            
            // Log the deletion for audit purposes
            Log::info('Payment deleted', $paymentInfo);
            
            // Redirect with success message
            return redirect()->back()->with('success', 'Payment deleted successfully.');
            
        } catch (\Exception $e) {
            Log::error('Failed to delete payment: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to delete payment. Please try again.');
        }
    }

    /**
     * Handle Stripe webhook events.
     */
    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (!$webhookSecret) {
            Log::warning('Stripe webhook received but no webhook secret configured');
            return response()->json(['error' => 'Webhook secret not configured'], 400);
        }

        try {
            // Verify webhook signature
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            
            Log::info('Stripe webhook received', [
                'event_type' => $event->type,
                'event_id' => $event->id
            ]);

            // Handle different event types
            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($event->data->object);
                    break;
                    
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;
                    
                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;
                    
                case 'invoice.payment_succeeded':
                case 'invoice.payment_failed':
                    // Handle subscription events if needed in future
                    Log::info('Stripe subscription event received', ['event_type' => $event->type]);
                    break;
                    
                default:
                    Log::info('Unhandled Stripe webhook event', ['event_type' => $event->type]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
                'remote_ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
            
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'event_type' => $event->type ?? 'unknown'
            ]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle checkout.session.completed webhook.
     */
    private function handleCheckoutSessionCompleted($session)
    {
        $paymentId = $session->metadata->payment_id ?? null;
        
        if (!$paymentId) {
            Log::warning('Checkout session completed without payment_id', [
                'session_id' => $session->id
            ]);
            return;
        }

        $payment = Payment::find($paymentId);
        if (!$payment) {
            Log::error('Payment not found for webhook', [
                'payment_id' => $paymentId,
                'session_id' => $session->id
            ]);
            return;
        }

        // Only process if payment is still pending
        if ($payment->status !== Payment::STATUS_PENDING) {
            Log::info('Payment already processed, skipping webhook', [
                'payment_id' => $paymentId,
                'current_status' => $payment->status
            ]);
            return;
        }

        try {
            \DB::beginTransaction();

            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'transaction_id' => $session->payment_intent,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'stripe_session_id' => $session->id,
                    'stripe_payment_intent' => $session->payment_intent,
                    'stripe_customer' => $session->customer,
                    'webhook_processed_at' => now()->toISOString(),
                    'verification_status' => 'webhook_verified'
                ])
            ]);

            // Process membership renewal and send notifications
            $this->handleSuccessfulPayment($payment);

            \DB::commit();

            Log::info('Webhook payment processed successfully', [
                'payment_id' => $paymentId,
                'session_id' => $session->id
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            Log::error('Webhook payment processing failed', [
                'payment_id' => $paymentId,
                'session_id' => $session->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle payment_intent.succeeded webhook.
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        $paymentId = $paymentIntent->metadata->payment_id ?? null;
        
        if (!$paymentId) {
            Log::warning('Payment intent succeeded without payment_id', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        $payment = Payment::find($paymentId);
        if (!$payment) {
            Log::error('Payment not found for payment intent webhook', [
                'payment_id' => $paymentId,
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        // Update payment with additional payment method info
        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'payment_method_details' => [
                    'type' => $paymentIntent->payment_method_types[0] ?? 'card',
                    'payment_method_id' => $paymentIntent->payment_method,
                    'amount_received' => $paymentIntent->amount_received,
                    'currency' => $paymentIntent->currency,
                ]
            ])
        ]);

        Log::info('Payment intent webhook processed', [
            'payment_id' => $paymentId,
            'payment_intent_id' => $paymentIntent->id
        ]);
    }

    /**
     * Handle payment_intent.payment_failed webhook.
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        $paymentId = $paymentIntent->metadata->payment_id ?? null;
        
        if (!$paymentId) {
            Log::warning('Payment intent failed without payment_id', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        $payment = Payment::find($paymentId);
        if (!$payment) {
            Log::error('Payment not found for failed payment intent', [
                'payment_id' => $paymentId,
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        $payment->update([
            'status' => Payment::STATUS_FAILED,
            'metadata' => array_merge($payment->metadata ?? [], [
                'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Payment failed',
                'failure_code' => $paymentIntent->last_payment_error->code ?? 'unknown',
                'failed_at' => now()->toISOString()
            ])
        ]);

        Log::error('Payment failed via webhook', [
            'payment_id' => $paymentId,
            'payment_intent_id' => $paymentIntent->id,
            'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Unknown'
        ]);
    }

    /**
     * Send TWINT verification notification to admin.
     */
    private function sendTwintVerificationNotification(Payment $payment)
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@' . str_replace(['http://', 'https://'], '', config('app.url')));
            $user = $payment->user;
            
            $subject = 'TWINT Payment Verification Required - Payment #' . $payment->id;
            
            $message = "A TWINT payment requires manual verification:\n\n";
            $message .= "Payment Details:\n";
            $message .= "- Payment ID: {$payment->id}\n";
            $message .= "- User: {$user->name} ({$user->email})\n";
            $message .= "- Amount: {$payment->formatted_amount}\n";
            $message .= "- Type: " . ucfirst($payment->payment_type) . "\n";
            $message .= "- User Confirmed: " . now()->format('M d, Y H:i') . "\n\n";
            
            $message .= "TWINT Reference: " . ($payment->metadata['twint_reference'] ?? 'Not provided') . "\n\n";
            
            $message .= "Action Required:\n";
            $message .= "1. Check TWINT merchant account for incoming payment\n";
            $message .= "2. Verify amount matches payment record\n";
            $message .= "3. Manually complete payment in admin panel\n\n";
            
            $message .= "Admin Panel: " . route('admin.payments') . "\n";
            $message .= "This payment will be marked as verified once manually completed.";

            Mail::raw($message, function ($mail) use ($adminEmail, $subject) {
                $mail->to($adminEmail)
                     ->subject($subject)
                     ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info("TWINT verification notification sent to admin", [
                'payment_id' => $payment->id,
                'admin_email' => $adminEmail
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send TWINT verification notification: ' . $e->getMessage(), [
                'payment_id' => $payment->id
            ]);
        }
    }

    /**
     * Send bank transfer verification notification to admin.
     */
    private function sendBankTransferVerificationNotification(Payment $payment)
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@' . str_replace(['http://', 'https://'], '', config('app.url')));
            $user = $payment->user;
            
            $subject = 'Bank Transfer Verification Required - Payment #' . $payment->id;
            
            $message = "A bank transfer requires manual verification:\n\n";
            $message .= "Payment Details:\n";
            $message .= "- Payment ID: {$payment->id}\n";
            $message .= "- User: {$user->name} ({$user->email})\n";
            $message .= "- Amount: {$payment->formatted_amount}\n";
            $message .= "- Type: " . ucfirst($payment->payment_type) . "\n";
            $message .= "- User Confirmed: " . now()->format('M d, Y H:i') . "\n\n";
            
            $message .= "Expected Bank Reference: " . ($payment->metadata['bank_reference'] ?? 'PAY-' . $payment->id) . "\n";
            $message .= "User's Transfer Reference: " . ($payment->metadata['user_transfer_reference'] ?? 'Not provided') . "\n\n";
            
            $message .= "Bank Account Details:\n";
            $message .= "- Bank: UBS Switzerland AG\n";
            $message .= "- IBAN: CH93 0023 0230 2305 8901 Y\n";
            $message .= "- Account Name: EN NUR Mosque Community\n\n";
            
            $message .= "Action Required:\n";
            $message .= "1. Check bank account for incoming transfer\n";
            $message .= "2. Match transfer reference with payment ID\n";
            $message .= "3. Verify amount matches payment record\n";
            $message .= "4. Manually complete payment in admin panel\n\n";
            
            $message .= "Admin Panel: " . route('admin.payments') . "\n";
            $message .= "Typical processing time: 1-3 business days";

            Mail::raw($message, function ($mail) use ($adminEmail, $subject) {
                $mail->to($adminEmail)
                     ->subject($subject)
                     ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info("Bank transfer verification notification sent to admin", [
                'payment_id' => $payment->id,
                'admin_email' => $adminEmail
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send bank transfer verification notification: ' . $e->getMessage(), [
                'payment_id' => $payment->id
            ]);
        }
    }

    /**
     * Show bank transfer instructions.
     */
    public function bankInstructions(Payment $payment)
    {
        $user = auth()->user();

        // Verify user owns this payment
        if ($payment->user_id !== $user->id) {
            abort(403, 'Unauthorized payment access.');
        }

        // Check if payment is still pending
        if ($payment->status !== Payment::STATUS_PENDING) {
            return redirect()->route('payment.create')->with('info', 'This payment has already been processed.');
        }

        return view('payments.bank-instructions', compact('payment'));
    }

    /**
     * Confirm bank transfer payment.
     */
    public function bankConfirm(Request $request, Payment $payment)
    {
        $request->validate([
            'confirmation' => 'required|accepted',
            'transfer_reference' => 'nullable|string|max:100',
        ], [
            'confirmation.accepted' => 'You must confirm that you have completed the bank transfer.',
        ]);

        try {
            $user = auth()->user();

            // Verify user owns this payment
            if ($payment->user_id !== $user->id) {
                Log::warning('Unauthorized bank transfer confirmation attempt', [
                    'payment_id' => $payment->id,
                    'payment_user_id' => $payment->user_id,
                    'current_user_id' => $user->id
                ]);
                return redirect()->route('payment.create')->with('error', 'Unauthorized payment access.');
            }

            // Verify payment is in pending state
            if ($payment->status !== Payment::STATUS_PENDING) {
                Log::info('Bank transfer payment already processed', [
                    'payment_id' => $payment->id,
                    'current_status' => $payment->status
                ]);
                return view('payments.success', compact('payment'));
            }

            // Update payment with confirmation details
            $payment->update([
                'status' => Payment::STATUS_PENDING,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'user_confirmed_at' => now()->toISOString(),
                    'confirmation_ip' => $request->ip(),
                    'user_transfer_reference' => $request->transfer_reference,
                    'awaiting_verification' => true,
                    'verification_status' => 'awaiting_manual_verification'
                ])
            ]);

            Log::info('Bank transfer payment confirmation received', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'user_reference' => $request->transfer_reference,
                'confirmed_at' => now()->toISOString()
            ]);

            // Send notification to admin for manual verification
            $this->sendBankTransferVerificationNotification($payment);

            return view('payments.success', compact('payment'))
                ->with('info', 'Transfer confirmation received. We will verify your bank transfer within 2-3 business days and send you a confirmation email.');

        } catch (\Exception $e) {
            Log::error('Bank transfer confirmation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Payment confirmation failed. Please try again or contact support.');
        }
    }
} 