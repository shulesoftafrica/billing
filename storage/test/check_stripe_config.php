<?php
/**
 * Stripe Configuration Diagnostic Script
 * Run: php check_stripe_config.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n====== STRIPE CONFIGURATION CHECK ======\n\n";

// Check publishable key
$publishableKey = config('services.stripe.publishable_key');
$secretKey = config('services.stripe.secret');

echo "1. Publishable Key:\n";
if (empty($publishableKey)) {
    echo "   ❌ MISSING - Not configured in .env\n";
} else {
    $keyType = substr($publishableKey, 0, 7);
    $keyPreview = substr($publishableKey, 0, 20) . '...';
    echo "   ✓ Found: {$keyPreview}\n";
    echo "   Mode: " . ($keyType === 'pk_live' ? "🔴 LIVE" : "🟡 TEST") . "\n";
}

echo "\n2. Secret Key:\n";
if (empty($secretKey)) {
    echo "   ❌ MISSING - Not configured in .env\n";
} else {
    $keyType = substr($secretKey, 0, 7);
    $keyPreview = substr($secretKey, 0, 20) . '...';
    echo "   ✓ Found: {$keyPreview}\n";
    echo "   Mode: " . ($keyType === 'sk_live' ? "🔴 LIVE" : "🟡 TEST") . "\n";
}

// Check if keys match
echo "\n3. Key Mode Match:\n";
if (!empty($publishableKey) && !empty($secretKey)) {
    $pubMode = substr($publishableKey, 3, 4); // 'live' or 'test'
    $secMode = substr($secretKey, 3, 4);
    
    if ($pubMode === $secMode) {
        echo "   ✓ Keys match ({$pubMode} mode)\n";
    } else {
        echo "   ❌ KEY MISMATCH DETECTED!\n";
        echo "   Publishable: {$pubMode}\n";
        echo "   Secret: {$secMode}\n";
        echo "   ⚠️  THIS IS CAUSING YOUR 400 ERROR!\n";
    }
}

// Check recent control numbers
echo "\n4. Recent Control Numbers:\n";
try {
    $controlNumbers = \App\Models\ControlNumber::with('organizationPaymentGatewayIntegration.paymentGateway')
        ->latest()
        ->limit(5)
        ->get();
    
    if ($controlNumbers->isEmpty()) {
        echo "   No control numbers found\n";
    } else {
        foreach ($controlNumbers as $cn) {
            $metadata = json_decode($cn->metadata, true) ?? [];
            $clientSecret = $metadata['client_secret'] ?? 'N/A';
            $paymentIntentId = $metadata['payment_intent_id'] ?? 'N/A';
            $gateway = $cn->organizationPaymentGatewayIntegration?->paymentGateway?->name ?? 'Unknown';
            
            $age = $cn->created_at->diffInHours(now());
            $isExpired = $age > 23;
            
            echo "\n   Control Number #{$cn->id}:\n";
            echo "   - Gateway: {$gateway}\n";
            echo "   - Created: {$cn->created_at->diffForHumans()}\n";
            echo "   - Age: {$age} hours " . ($isExpired ? "❌ EXPIRED" : "✓ Valid") . "\n";
            echo "   - Payment Intent: " . substr($paymentIntentId, 0, 20) . "...\n";
            
            if ($clientSecret !== 'N/A') {
                $secretMode = substr($clientSecret, 0, 10);
                echo "   - Client Secret: {$secretMode}...\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "   Error fetching control numbers: {$e->getMessage()}\n";
}

// Check for test invoice
echo "\n\n5. Test Invoice Check:\n";
try {
    $testInvoice = \App\Models\Invoice::with(['invoiceItems', 'customer'])
        ->where('status', 'pending')
        ->latest()
        ->first();
    
    if ($testInvoice) {
        echo "   Found pending invoice: #{$testInvoice->id}\n";
        echo "   Invoice Number: {$testInvoice->invoice_number}\n";
        echo "   Total: {$testInvoice->currency} " . number_format($testInvoice->total, 2) . "\n";
        echo "   Customer: {$testInvoice->customer->name}\n";
        
        // Check if this invoice has a control number
        $invoiceControlNumbers = \App\Models\ControlNumber::where(function($q) use ($testInvoice) {
            $q->where('customer_id', $testInvoice->customer_id)
              ->whereRaw("metadata::text LIKE ?", ['%"invoice_id":' . $testInvoice->id . '%']);
        })->get();
        
        echo "   Control Numbers: " . $invoiceControlNumbers->count() . "\n";
        
        if ($invoiceControlNumbers->count() > 0) {
            $latestCN = $invoiceControlNumbers->sortByDesc('created_at')->first();
            $age = $latestCN->created_at->diffInHours(now());
            echo "   Latest CN Age: {$age} hours\n";
            
            if ($age > 23) {
                echo "   ⚠️  Payment intent is EXPIRED! Create a new one.\n";
            }
        }
    } else {
        echo "   No pending invoices found\n";
    }
} catch (\Exception $e) {
    echo "   Error: {$e->getMessage()}\n";
}

// Recommendations
echo "\n\n====== RECOMMENDATIONS ======\n\n";

if (empty($publishableKey) || empty($secretKey)) {
    echo "❌ Configure your .env file with Stripe keys:\n";
    echo "   STRIPE_PUBLISHABLE_KEY=pk_test_your_key_here\n";
    echo "   STRIPE_SECRET_KEY=sk_test_your_key_here\n\n";
}

$pubMode = !empty($publishableKey) ? substr($publishableKey, 3, 4) : '';
$secMode = !empty($secretKey) ? substr($secretKey, 3, 4) : '';

if ($pubMode !== $secMode && !empty($pubMode) && !empty($secMode)) {
    echo "❌ FIX KEY MISMATCH:\n";
    echo "   Your publishable and secret keys are from different modes!\n";
    echo "   Either use both TEST keys or both LIVE keys.\n\n";
    echo "   After fixing, run:\n";
    echo "   php artisan config:clear\n\n";
}

$hasExpiredCN = false;
if (isset($controlNumbers)) {
    foreach ($controlNumbers as $cn) {
        if ($cn->created_at->diffInHours(now()) > 23) {
            $hasExpiredCN = true;
            break;
        }
    }
}

if ($hasExpiredCN) {
    echo "⚠️  EXPIRED PAYMENT INTENTS FOUND:\n";
    echo "   Delete old control numbers and create fresh payment intents.\n";
    echo "   In tinker: \\App\\Models\\ControlNumber::where('created_at', '<', now()->subHours(24))->delete();\n\n";
}

echo "✓ To test the payment page, visit:\n";
echo "  http://localhost/billing/pay/{invoice_id}\n\n";

echo "====================================\n\n";
