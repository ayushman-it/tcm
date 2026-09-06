<?php
/**
 * Migration: Add Referral Columns to Payment Submissions
 * 
 * This script adds referral_code and referrer_id columns to the payment_submissions table
 * to support the referral system during payment submission.
 * 
 * Usage: php migrate_referral_columns.php
 */

require __DIR__ . '/src/bootstrap.php';

use TCM\Core\Database;

try {
    echo "🔧 Starting Referral Columns Migration...\n\n";

    // Check if table exists
    $tableExists = (bool) Database::scalar(
        "SELECT COUNT(*) FROM information_schema.tables 
         WHERE table_schema = DATABASE() AND table_name = 'payment_submissions'"
    );

    if (!$tableExists) {
        echo "❌ Error: payment_submissions table does not exist.\n";
        echo "   Please run database/payment_system.sql first.\n";
        exit(1);
    }

    // Check if columns already exist
    $referralCodeExists = (bool) Database::scalar(
        "SELECT COUNT(*) FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND table_name = 'payment_submissions' 
         AND column_name = 'referral_code'"
    );

    $referrerIdExists = (bool) Database::scalar(
        "SELECT COUNT(*) FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND table_name = 'payment_submissions' 
         AND column_name = 'referrer_id'"
    );

    if ($referralCodeExists && $referrerIdExists) {
        echo "✅ Columns already exist. Nothing to migrate.\n";
        exit(0);
    }

    echo "📋 Adding referral columns to payment_submissions table...\n";

    // Add referral_code column
    if (!$referralCodeExists) {
        Database::run(
            "ALTER TABLE payment_submissions 
             ADD COLUMN referral_code VARCHAR(50) DEFAULT NULL AFTER transaction_ref"
        );
        echo "   ✓ Added referral_code column\n";
    } else {
        echo "   → referral_code column already exists\n";
    }

    // Add referrer_id column
    if (!$referrerIdExists) {
        Database::run(
            "ALTER TABLE payment_submissions 
             ADD COLUMN referrer_id BIGINT UNSIGNED DEFAULT NULL AFTER referral_code"
        );
        echo "   ✓ Added referrer_id column\n";
    } else {
        echo "   → referrer_id column already exists\n";
    }

    // Add index on referrer_id
    $indexExists = (bool) Database::scalar(
        "SELECT COUNT(*) FROM information_schema.statistics 
         WHERE table_schema = DATABASE() 
         AND table_name = 'payment_submissions' 
         AND index_name = 'idx_referrer_id'"
    );

    if (!$indexExists && $referrerIdExists) {
        Database::run("ALTER TABLE payment_submissions ADD KEY idx_referrer_id (referrer_id)");
        echo "   ✓ Added index on referrer_id\n";
    } else {
        echo "   → Index on referrer_id already exists\n";
    }

    // Add foreign key constraint
    $fkExists = (bool) Database::scalar(
        "SELECT COUNT(*) FROM information_schema.key_column_usage 
         WHERE table_schema = DATABASE() 
         AND table_name = 'payment_submissions' 
         AND constraint_name = 'fk_ps_referrer'"
    );

    if (!$fkExists && $referrerIdExists) {
        Database::run(
            "ALTER TABLE payment_submissions 
             ADD CONSTRAINT fk_ps_referrer FOREIGN KEY (referrer_id) 
             REFERENCES users(id) ON DELETE SET NULL"
        );
        echo "   ✓ Added foreign key constraint\n";
    } else {
        echo "   → Foreign key constraint already exists\n";
    }

    echo "\n✅ Migration completed successfully!\n";
    echo "\n📊 Current table structure:\n";

    $columns = Database::all(
        "SELECT column_name, column_type, is_nullable, column_default 
         FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND table_name = 'payment_submissions' 
         AND column_name IN ('referral_code', 'referrer_id')
         ORDER BY ordinal_position"
    );

    foreach ($columns as $col) {
        echo "   - {$col['column_name']}: {$col['column_type']} ";
        echo "(" . ($col['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL') . ")\n";
    }

    echo "\n🎉 Payment submission form can now accept referral codes!\n";
    exit(0);

} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n";
    echo "   " . $e->getTraceAsString() . "\n";
    exit(1);
}
