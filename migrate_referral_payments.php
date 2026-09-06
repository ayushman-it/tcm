<?php
/**
 * Migration: Add referral code columns to payment_submissions table
 */

require __DIR__ . '/src/bootstrap.php';

use TCM\Core\Database;

echo "🚀 Starting migration: Add referral columns to payment_submissions...\n\n";

try {
    // Check if columns already exist
    $columnExists = Database::scalar(
        "SELECT COUNT(*) FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND table_name = 'payment_submissions' 
         AND column_name = 'referral_code'"
    );

    if ($columnExists) {
        echo "✅ Columns already exist. Migration skipped.\n";
        exit(0);
    }

    // Add referral_code column
    echo "📝 Adding referral_code column...\n";
    Database::run("
        ALTER TABLE payment_submissions
        ADD COLUMN referral_code VARCHAR(50) DEFAULT NULL AFTER transaction_ref
    ");
    echo "✅ referral_code column added\n\n";

    // Add referrer_id column
    echo "📝 Adding referrer_id column...\n";
    Database::run("
        ALTER TABLE payment_submissions
        ADD COLUMN referrer_id BIGINT UNSIGNED DEFAULT NULL AFTER referral_code,
        ADD KEY idx_referrer_id (referrer_id)
    ");
    echo "✅ referrer_id column added with index\n\n";

    // Add foreign key constraint
    echo "📝 Adding foreign key constraint...\n";
    Database::run("
        ALTER TABLE payment_submissions
        ADD CONSTRAINT fk_ps_referrer 
        FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE SET NULL
    ");
    echo "✅ Foreign key constraint added\n\n";

    echo "🎉 Migration completed successfully!\n";
    echo "\n";
    echo "Summary:\n";
    echo "  ✓ referral_code VARCHAR(50) - stores the referral code entered by user\n";
    echo "  ✓ referrer_id BIGINT - links to the user who owns the referral code\n";
    echo "  ✓ Foreign key constraint ensures data integrity\n";
    echo "\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
