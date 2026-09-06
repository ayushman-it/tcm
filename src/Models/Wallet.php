<?php

declare(strict_types=1);

namespace TCM\Models;

use TCM\Core\Database;
use TCM\Core\FirebaseNotification;

/**
 * Student Wallet — referral earnings & withdrawal requests.
 *
 * Tables:
 *   wallets              (user_id, balance)
 *   wallet_transactions  (wallet_id, user_id, type, amount, description, ref_id, created_at)
 *   withdrawal_requests  (user_id, amount, upi_id, status, admin_note, processed_at, created_at)
 */
final class Wallet extends Model
{
    protected static string $table = 'wallets';

    // ── Minimum balance required to raise withdrawal ──────
    public const MIN_WITHDRAWAL = 300;
    // ── Referral credit per approved payment ──────────────
    public const REFERRAL_CREDIT = 100;

    /* ── Get or create wallet ─────────────────────────── */
    public static function forUser(int $userId): array
    {
        if (!self::tableReady()) return ['id' => 0, 'user_id' => $userId, 'balance' => 0.00];
        $w = Database::first('SELECT * FROM wallets WHERE user_id = ?', [$userId]);
        if ($w === null) {
            $id = Database::insert('wallets', ['user_id' => $userId, 'balance' => 0.00]);
            $w  = Database::first('SELECT * FROM wallets WHERE id = ?', [$id]);
        }
        return $w;
    }

    public static function balance(int $userId): float
    {
        if (!self::tableReady()) return 0.00;
        return (float) (Database::scalar('SELECT balance FROM wallets WHERE user_id = ?', [$userId]) ?? 0);
    }

    /* ── Credit ───────────────────────────────────────── */
    public static function credit(int $userId, float $amount, string $desc, string $refId = ''): void
    {
        if (!self::tableReady()) return;
        $wallet = self::forUser($userId);
        $newBal = (float) $wallet['balance'] + $amount;

        Database::update('wallets', ['balance' => $newBal], ['user_id' => $userId]);

        Database::insert('wallet_transactions', [
            'wallet_id'   => (int) $wallet['id'],
            'user_id'     => $userId,
            'type'        => 'credit',
            'amount'      => $amount,
            'description' => $desc,
            'ref_id'      => $refId ?: null,
            'balance_after' => $newBal,
        ]);

        // In-app + push notification
        Notification::ensureTable();
        Notification::forUser(
            $userId,
            '💰 ₹' . number_format($amount, 0) . ' added to your wallet!',
            $desc . ' | New balance: ₹' . number_format($newBal, 0),
            '💰',
            base_url('/student/wallet')
        );
        FirebaseNotification::notifyStudent(
            $userId,
            '💰 Wallet Credit: ₹' . number_format($amount, 0),
            $desc,
            ['tag' => 'wallet-credit'],
            base_url('/student/wallet')
        );
    }

    /* ── Debit (on withdrawal processing) ────────────── */
    public static function debit(int $userId, float $amount, string $desc): bool
    {
        if (!self::tableReady()) return false;
        $wallet = self::forUser($userId);
        if ((float) $wallet['balance'] < $amount) return false;
        $newBal = (float) $wallet['balance'] - $amount;

        Database::update('wallets', ['balance' => $newBal], ['user_id' => $userId]);

        Database::insert('wallet_transactions', [
            'wallet_id'     => (int) $wallet['id'],
            'user_id'       => $userId,
            'type'          => 'debit',
            'amount'        => $amount,
            'description'   => $desc,
            'ref_id'        => null,
            'balance_after' => $newBal,
        ]);
        return true;
    }

    /* ── Transactions ─────────────────────────────────── */
    public static function transactions(int $userId, int $limit = 30): array
    {
        if (!self::tableReady()) return [];
        return Database::all(
            'SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . $limit,
            [$userId]
        );
    }

    /* ── Withdrawal requests ──────────────────────────── */
    public static function requestWithdrawal(int $userId, float $amount, string $upiId): bool
    {
        if (!self::tableReady()) return false;
        if ($amount < self::MIN_WITHDRAWAL) return false;
        if (self::balance($userId) < $amount) return false;

        // Only one pending request at a time
        $pending = Database::scalar(
            "SELECT id FROM withdrawal_requests WHERE user_id = ? AND status = 'pending'",
            [$userId]
        );
        if ($pending) return false;

        Database::insert('withdrawal_requests', [
            'user_id' => $userId,
            'amount'  => $amount,
            'upi_id'  => $upiId,
            'status'  => 'pending',
        ]);

        // Notify admins
        Notification::ensureTable();
        $user = Database::first('SELECT name FROM users WHERE id = ?', [$userId]);
        Notification::toAdmins(
            '💸 Withdrawal Request — ' . ($user['name'] ?? 'Student'),
            '₹' . number_format($amount, 0) . ' withdrawal to UPI: ' . $upiId,
            '💸',
            base_url('/admin/wallet')
        );
        FirebaseNotification::notifyAdmins(
            '💸 Withdrawal Request',
            ($user['name'] ?? 'Student') . ' wants to withdraw ₹' . number_format($amount, 0),
            ['tag' => 'withdrawal-request'],
            base_url('/admin/wallet')
        );

        return true;
    }

    public static function withdrawalRequests(int $userId): array
    {
        if (!self::tableReady()) return [];
        return Database::all(
            'SELECT * FROM withdrawal_requests WHERE user_id = ? ORDER BY created_at DESC',
            [$userId]
        );
    }

    public static function allWithdrawalRequests(array $filters = []): array
    {
        if (!self::tableReady()) return [];
        $sql = "SELECT wr.*, u.name AS student_name, u.email AS student_email,
                       w.balance AS current_balance
                FROM withdrawal_requests wr
                JOIN users u ON u.id = wr.user_id
                LEFT JOIN wallets w ON w.user_id = wr.user_id
                WHERE 1";
        $params = [];
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= ' AND wr.status = ?';
            $params[] = $filters['status'];
        }
        $sql .= " ORDER BY FIELD(wr.status,'pending','approved','rejected'), wr.created_at DESC";
        return Database::all($sql, $params);
    }

    /* ── Credit referral when payment approved ────────── */
    public static function creditReferral(int $newStudentId, int $paymentId): void
    {
        if (!self::tableReady()) return;

        // Find referral code used in this payment
        $refCode = Database::scalar(
            'SELECT referral_code FROM payment_submissions WHERE id = ?',
            [$paymentId]
        );
        if (!$refCode) return;

        // Find the student who owns this referral code
        $referrer = Database::first(
            "SELECT id, name FROM users WHERE referral_id = ? AND role = 'student' AND status = 'active'",
            [$refCode]
        );
        if ($referrer === null) return;
        if ((int) $referrer['id'] === $newStudentId) return; // can't refer yourself

        // Credit once per payment
        $alreadyCredited = Database::scalar(
            "SELECT id FROM wallet_transactions WHERE ref_id = ? AND type = 'credit'",
            ['referral-payment-' . $paymentId]
        );
        if ($alreadyCredited) return;

        $payer = Database::first('SELECT name FROM users WHERE id = ?', [$newStudentId]);
        self::credit(
            (int) $referrer['id'],
            self::REFERRAL_CREDIT,
            'Referral bonus — ' . ($payer['name'] ?? 'New student') . ' joined via your code!',
            'referral-payment-' . $paymentId
        );
    }

    /* ── Table guard ──────────────────────────────────── */
    private static ?bool $ready = null;

    public static function tableReady(): bool
    {
        if (self::$ready !== null) return self::$ready;
        self::$ready = (bool) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'wallets'"
        );
        return self::$ready;
    }

    public static function ensureTables(): void
    {
        if (self::tableReady()) return;

        Database::run("CREATE TABLE IF NOT EXISTS wallets (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id    BIGINT UNSIGNED NOT NULL,
            balance    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_wallet_user (user_id),
            CONSTRAINT fk_wallet_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        Database::run("CREATE TABLE IF NOT EXISTS wallet_transactions (
            id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wallet_id     BIGINT UNSIGNED NOT NULL,
            user_id       BIGINT UNSIGNED NOT NULL,
            type          ENUM('credit','debit') NOT NULL,
            amount        DECIMAL(10,2)   NOT NULL,
            description   VARCHAR(255)    NOT NULL,
            ref_id        VARCHAR(100)    DEFAULT NULL,
            balance_after DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_wt_user (user_id),
            KEY idx_wt_wallet (wallet_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        Database::run("CREATE TABLE IF NOT EXISTS withdrawal_requests (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id      BIGINT UNSIGNED NOT NULL,
            amount       DECIMAL(10,2)   NOT NULL,
            upi_id       VARCHAR(150)    NOT NULL,
            status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            admin_note   VARCHAR(255)    DEFAULT NULL,
            processed_at DATETIME        DEFAULT NULL,
            created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_wr_user   (user_id),
            KEY idx_wr_status (status),
            CONSTRAINT fk_wr_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        self::$ready = true;
    }
}
