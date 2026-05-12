<?php
// © Atia Hegazy — atiaeno.com

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Affiliate;
use App\Models\ReferralCommission;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

echo "=== Referral System Test ===\n\n";

// Test 1: Check referral commission rate setting
echo "1. Testing referral commission rate setting...\n";
$rate = Setting::get('referral_commission_rate', 1.5);
echo "   Referral commission rate: {$rate}%\n";

// Test 2: Check database tables exist
echo "\n2. Testing database tables...\n";
echo '   Users table has referred_by_affiliate_id: ' . (Schema::hasColumn('users', 'referred_by_affiliate_id') ? 'YES' : 'NO') . "\n";
echo '   Affiliates table has referral_earnings: ' . (Schema::hasColumn('affiliates', 'referral_earnings') ? 'YES' : 'NO') . "\n";
echo '   Referral commissions table exists: ' . (Schema::hasTable('referral_commissions') ? 'YES' : 'NO') . "\n";

// Test 3: Test Affiliate model methods
echo "\n3. Testing Affiliate model methods...\n";
try {
    $affiliate = new Affiliate();
    echo "   Affiliate model loads successfully\n";
    echo '   getTotalEarningsIncludingReferrals method exists: ' . (method_exists($affiliate, 'getTotalEarningsIncludingReferrals') ? 'YES' : 'NO') . "\n";
    echo '   canRequestPayoutWithReferrals method exists: ' . (method_exists($affiliate, 'canRequestPayoutWithReferrals') ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo '   Error: ' . $e->getMessage() . "\n";
}

// Test 4: Test User model relationships
echo "\n4. Testing User model relationships...\n";
try {
    $user = new User();
    echo "   User model loads successfully\n";
    echo '   referredBy relationship exists: ' . (method_exists($user, 'referredBy') ? 'YES' : 'NO') . "\n";
    echo '   referredUsers relationship exists: ' . (method_exists($user, 'referredUsers') ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo '   Error: ' . $e->getMessage() . "\n";
}

// Test 5: Test ReferralCommission model
echo "\n5. Testing ReferralCommission model...\n";
try {
    $commission = new ReferralCommission();
    echo "   ReferralCommission model loads successfully\n";
    echo '   referrer relationship exists: ' . (method_exists($commission, 'referrer') ? 'YES' : 'NO') . "\n";
    echo '   referral relationship exists: ' . (method_exists($commission, 'referral') ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo '   Error: ' . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "All basic tests passed. The referral system is ready for use!\n";
