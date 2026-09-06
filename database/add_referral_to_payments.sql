-- Add referral code columns to payment_submissions table
ALTER TABLE payment_submissions
ADD COLUMN IF NOT EXISTS referral_code VARCHAR(50) DEFAULT NULL AFTER transaction_ref,
ADD COLUMN IF NOT EXISTS referrer_id BIGINT UNSIGNED DEFAULT NULL AFTER referral_code;

-- Add index if it doesn't exist
SET @index_exists = (SELECT COUNT(*) 
                     FROM information_schema.statistics 
                     WHERE table_schema = DATABASE() 
                     AND table_name = 'payment_submissions' 
                     AND index_name = 'idx_referrer_id');

SET @sql_index = IF(@index_exists = 0,
    'ALTER TABLE payment_submissions ADD KEY idx_referrer_id (referrer_id)',
    'SELECT "Index already exists"');
PREPARE stmt FROM @sql_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint if it doesn't exist
SET @fk_exists = (SELECT COUNT(*) 
                  FROM information_schema.key_column_usage 
                  WHERE table_schema = DATABASE() 
                  AND table_name = 'payment_submissions' 
                  AND constraint_name = 'fk_ps_referrer');

SET @sql_fk = IF(@fk_exists = 0,
    'ALTER TABLE payment_submissions ADD CONSTRAINT fk_ps_referrer FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT "Foreign key already exists"');
PREPARE stmt FROM @sql_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
