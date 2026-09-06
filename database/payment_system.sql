-- =====================================================================
-- TCM Payment System & Student ID Migration
-- Run this after the main schema.sql
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Add student_id and referral_id to users table
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS student_id VARCHAR(20) DEFAULT NULL AFTER onboarded,
    ADD COLUMN IF NOT EXISTS referral_id VARCHAR(12) DEFAULT NULL AFTER student_id,
    ADD UNIQUE KEY IF NOT EXISTS uniq_student_id (student_id),
    ADD UNIQUE KEY IF NOT EXISTS uniq_referral_id (referral_id);

-- Payment submissions: students upload proof, admin approves
CREATE TABLE IF NOT EXISTS payment_submissions (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED NOT NULL,
    item_type       ENUM('course','event','program') NOT NULL,
    item_id         BIGINT UNSIGNED NOT NULL,
    item_title      VARCHAR(200)    NOT NULL,
    amount          DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    payment_method  ENUM('cash','upi','bank_transfer','online','other') NOT NULL DEFAULT 'upi',
    payment_date    DATE            NOT NULL,
    screenshot      VARCHAR(255)    DEFAULT NULL,   -- uploaded screenshot path
    reason          VARCHAR(255)    DEFAULT NULL,   -- reason / note from student
    transaction_ref VARCHAR(120)    DEFAULT NULL,   -- UTR / transaction ID
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_note      VARCHAR(255)    DEFAULT NULL,
    receipt_number  VARCHAR(50)     DEFAULT NULL,   -- generated on approval
    reviewed_by     BIGINT UNSIGNED DEFAULT NULL,
    reviewed_at     DATETIME        DEFAULT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ps_user   (user_id),
    KEY idx_ps_status (status),
    CONSTRAINT fk_ps_user     FOREIGN KEY (user_id)     REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_ps_reviewer FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enquiry/interest leads captured via popup lead form before purchase
-- (reuses existing `leads` table - no new table needed)

SET FOREIGN_KEY_CHECKS = 1;
