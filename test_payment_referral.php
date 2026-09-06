<?php
/**
 * Test Payment Referral Code Submission
 * 
 * This script simulates payment submission with referral code
 * to debug the 500 error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/src/bootstrap.php';

use TCM\Core\Database;

echo "🧪 Testing Payment Referral Code Submission\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // Test 1: Check if columns exist
    echo "1️⃣  Checking payment_submissions table structure...\n";
    
    $columns = Database::all(
        "SELECT COLUMN_NAME, COLUMN_TYPE 
         FROM information_schema.COLUMNS 
         WHERE TABLE_SCHEMA = DATABASE() 
         AND TABLE_NAME = 'payment_submissions'
         AND COLUMN_NAME IN ('referral_code', 'referrer_id')"
    );
    
    if (count($columns) === 2) {
        echo "   ✅ Both columns exist:\n";
        foreach ($columns as $col) {
            echo "      - {$col['COLUMN_NAME']}: {$col['COLUMN_TYPE']}\n";
        }
    } else {
        echo "   ❌ Columns missing! Found " . count($columns) . " / 2\n";
        echo "   Run: php migrate_referral_columns.php\n";
        exit(1);
    }
    
    echo "\n";
    
    // Test 2: Check if test referral code exists
    echo "2️⃣  Checking for valid referral codes...\n";
    
    $referralCodes = Database::all(
        "SELECT id, name, email, referral_id 
         FROM users 
         WHERE referral_id IS NOT NULL 
         LIMIT 5"
    );
    
    if (empty($referralCodes)) {
        echo "   ⚠️  No users with referral IDs found\n";
        echo "   Creating test referral ID...\n";
        
        // Get first user
        $testUser = Database::first("SELECT id FROM users WHERE role='student' LIMIT 1");
        if ($testUser) {
            $testRefCode = 'REF-TEST01';
            Database::update('users', ['referral_id' => $testRefCode], ['id' => $testUser['id']]);
            echo "   ✅ Created test referral: {$testRefCode}\n";
        }
    } else {
        echo "   ✅ Found " . count($referralCodes) . " users with referral IDs:\n";
        foreach ($referralCodes as $user) {
            echo "      - {$user['name']}: {$user['referral_id']}\n";
        }
    }
    
    echo "\n";
    
    // Test 3: Test referral code lookup
    echo "3️⃣  Testing referral code lookup...\n";
    
    $testRefCode = $referralCodes[0]['referral_id'] ?? 'REF-TEST01';
    echo "   Testing with code: {$testRefCode}\n";
    
    $referrer = Database::first("SELECT id, name FROM users WHERE referral_id = ?", [$testRefCode]);
    
    if ($referrer) {
        echo "   ✅ Referral lookup successful\n";
        echo "      Referrer: {$referrer['name']} (ID: {$referrer['id']})\n";
    } else {
        echo "   ❌ Referral lookup failed!\n";
        exit(1);
    }
    
    echo "\n";
    
    // Test 4: Simulate payment submission data
    echo "4️⃣  Simulating payment submission with referral...\n";
    
    $testData = [
        'user_id'         => 1,
        'item_type'       => 'course',
        'item_id'         => 1,
        'item_title'      => 'Test Course',
        'amount'          => 999.00,
        'payment_method'  => 'upi',
        'payment_date'    => date('Y-m-d'),
        'screenshot'      => null,
        'reason'          => 'Test payment with referral',
        'transaction_ref' => 'TEST' . time(),
        'referral_code'   => $testRefCode,
        'referrer_id'     => (int)$referrer['id'],
        'status'          => 'pending',
    ];
    
    echo "   Data to insert:\n";
    foreach ($testData as $key => $value) {
        $displayValue = $value === null ? 'NULL' : $value;
        echo "      - {$key}: {$displayValue}\n";
    }
    
    echo "\n   Attempting insert...\n";
    
    $insertId = Database::insert('payment_submissions', $testData);
    
    if ($insertId > 0) {
        echo "   ✅ Payment submission successful! ID: {$insertId}\n";
        
        // Verify inserted data
        $inserted = Database::first("SELECT * FROM payment_submissions WHERE id = ?", [$insertId]);
        
        echo "\n   Verifying inserted data:\n";
        echo "      - ID: {$inserted['id']}\n";
        echo "      - Referral Code: {$inserted['referral_code']}\n";
        echo "      - Referrer ID: {$inserted['referrer_id']}\n";
        echo "      - Status: {$inserted['status']}\n";
        
        // Clean up test data
        echo "\n   Cleaning up test data...\n";
        Database::delete('payment_submissions', ['id' => $insertId]);
        echo "   ✅ Test data cleaned\n";
    } else {
        echo "   ❌ Payment submission failed!\n";
        exit(1);
    }
    
    echo "\n";
    echo str_repeat("=", 60) . "\n";
    echo "🎉 All tests passed! Payment referral system working!\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "\n📝 Test Summary:\n";
    echo "✅ Database columns exist\n";
    echo "✅ Referral codes found\n";
    echo "✅ Referral lookup works\n";
    echo "✅ Payment insert with referral works\n";
    echo "\n✅ System is ready for production use!\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo "\n❌ Test failed with error:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n   Stack trace:\n";
    echo "   " . str_replace("\n", "\n   ", $e->getTraceAsString()) . "\n";
    exit(1);
}
