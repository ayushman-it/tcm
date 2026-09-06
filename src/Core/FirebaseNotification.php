<?php

declare(strict_types=1);

namespace TCM\Core;

/**
 * Firebase Cloud Messaging — HTTP v1 API
 *
 * Uses service account JWT auth (no legacy server key needed).
 * Service account file: storage/firebase-service-account.json
 *
 * All methods are crash-safe:
 *  - Returns false silently if service account not found
 *  - Returns false silently if fcm_tokens table doesn't exist yet
 */
final class FirebaseNotification
{
    private const FCM_V1_URL = 'https://fcm.googleapis.com/v1/projects/thecodemunk-21705/messages:send';
    private const SA_FILE    = __DIR__ . '/../../storage/firebase-service-account.json';

    /** Per-request OAuth2 token cache */
    private static ?string $accessToken     = null;
    private static int     $tokenExpiresAt  = 0;

    /** Per-request DB table cache */
    private static ?bool   $tableExists     = null;

    // ─────────────────────────────────────────────────────────────
    //  PUBLIC SEND METHODS
    // ─────────────────────────────────────────────────────────────

    public static function notifyAdmins(
        string $title,
        string $body,
        array  $data     = [],
        string $clickUrl = ''
    ): bool {
        if (!self::tableReady()) return false;
        $tokens = self::getTokensByRole('admin');
        return self::sendToTokens($tokens, $title, $body, $data, $clickUrl);
    }

    public static function notifyStudent(
        int    $userId,
        string $title,
        string $body,
        array  $data     = [],
        string $clickUrl = ''
    ): bool {
        if (!self::tableReady()) return false;
        $tokens = self::getTokensByUser($userId);
        return self::sendToTokens($tokens, $title, $body, $data, $clickUrl);
    }

    public static function notifyAllStudents(
        string $title,
        string $body,
        array  $data     = [],
        string $clickUrl = ''
    ): bool {
        if (!self::tableReady()) return false;
        $tokens = self::getTokensByRole('student');
        return self::sendToTokens($tokens, $title, $body, $data, $clickUrl);
    }

    public static function broadcast(
        string $title,
        string $body,
        array  $data     = [],
        string $clickUrl = ''
    ): bool {
        if (!self::tableReady()) return false;
        $rows   = Database::all('SELECT DISTINCT token FROM fcm_tokens WHERE token IS NOT NULL');
        $tokens = array_column($rows, 'token');
        return self::sendToTokens($tokens, $title, $body, $data, $clickUrl);
    }

    public static function saveToken(int $userId, string $token): void
    {
        if (empty($token)) return;
        if (!self::tableReady()) self::createTable();

        Database::run(
            'DELETE FROM fcm_tokens WHERE token = ? AND user_id != ?',
            [$token, $userId]
        );
        Database::run(
            'INSERT INTO fcm_tokens (user_id, token)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), updated_at = NOW()',
            [$userId, $token]
        );
    }

    // ─────────────────────────────────────────────────────────────
    //  CORE SEND LOGIC
    // ─────────────────────────────────────────────────────────────

    /**
     * Send to multiple tokens (one FCM request per token for v1 API).
     * FCM v1 does not support multi-cast — we fan-out individually.
     */
    private static function sendToTokens(
        array  $tokens,
        string $title,
        string $body,
        array  $data,
        string $clickUrl
    ): bool {
        if (empty($tokens)) return false;

        $token = self::getAccessToken();
        if (!$token) return false;

        $sent = 0;
        $clickUrl = $clickUrl ?: base_url('/admin');

        // Extra data as string map (FCM v1 requires all values to be strings)
        $dataMap = [];
        foreach ($data as $k => $v) {
            $dataMap[(string)$k] = (string)$v;
        }
        $dataMap['click_url'] = $clickUrl;
        $dataMap['title']     = $title;
        $dataMap['body']      = $body;

        foreach (array_unique($tokens) as $deviceToken) {
            $payload = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => $dataMap,
                    'webpush' => [
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                            'icon'  => config('app.url') . '/uploads/tcm-icon.png',
                            'badge' => config('app.url') . '/uploads/tcm-icon.png',
                            'click_action' => $clickUrl,
                            'requireInteraction' => false,
                        ],
                        'fcm_options' => [
                            'link' => $clickUrl,
                        ],
                    ],
                    'android' => [
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => ['sound' => 'default'],
                        ],
                    ],
                ],
            ];

            $result = self::httpPost(self::FCM_V1_URL, $payload, $token);

            if (isset($result['name'])) {
                $sent++;
            } else {
                // Remove invalid/expired tokens
                if (isset($result['error']['status']) &&
                    in_array($result['error']['status'], ['UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
                    Database::run('DELETE FROM fcm_tokens WHERE token = ?', [$deviceToken]);
                }
                error_log('[FCM v1] Send failed: ' . json_encode($result['error'] ?? $result));
            }
        }

        return $sent > 0;
    }

    // ─────────────────────────────────────────────────────────────
    //  OAUTH2 — GET ACCESS TOKEN FROM SERVICE ACCOUNT
    // ─────────────────────────────────────────────────────────────

    private static function getAccessToken(): ?string
    {
        // Return cached token if still valid (with 60s buffer)
        if (self::$accessToken !== null && time() < self::$tokenExpiresAt - 60) {
            return self::$accessToken;
        }

        // Load service account
        if (!is_file(self::SA_FILE)) {
            error_log('[FCM] Service account file not found: ' . self::SA_FILE);
            return null;
        }

        $sa = json_decode(file_get_contents(self::SA_FILE), true);
        if (!$sa || empty($sa['private_key']) || empty($sa['client_email'])) {
            error_log('[FCM] Invalid service account JSON.');
            return null;
        }

        // Build JWT
        $now = time();
        $jwt = self::buildJwt($sa['client_email'], $sa['private_key'], $now);
        if (!$jwt) return null;

        // Exchange JWT for access token
        $response = self::httpPost(
            'https://oauth2.googleapis.com/token',
            [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ],
            null,
            'application/x-www-form-urlencoded'
        );

        if (empty($response['access_token'])) {
            error_log('[FCM] Failed to get access token: ' . json_encode($response));
            return null;
        }

        self::$accessToken    = $response['access_token'];
        self::$tokenExpiresAt = $now + (int)($response['expires_in'] ?? 3600);

        return self::$accessToken;
    }

    private static function buildJwt(string $email, string $privateKey, int $now): ?string
    {
        $header  = self::base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = self::base64url(json_encode([
            'iss'   => $email,
            'sub'   => $email,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $signingInput = $header . '.' . $payload;

        $key = openssl_pkey_get_private($privateKey);
        if (!$key) {
            error_log('[FCM] Failed to load private key.');
            return null;
        }

        $signature = '';
        if (!openssl_sign($signingInput, $signature, $key, OPENSSL_ALGO_SHA256)) {
            error_log('[FCM] Failed to sign JWT.');
            return null;
        }

        return $signingInput . '.' . self::base64url($signature);
    }

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // ─────────────────────────────────────────────────────────────
    //  HTTP HELPER
    // ─────────────────────────────────────────────────────────────

    private static function httpPost(
        string  $url,
        array   $data,
        ?string $bearerToken  = null,
        string  $contentType  = 'application/json'
    ): array {
        if (!function_exists('curl_init')) return [];

        $body    = $contentType === 'application/json'
            ? json_encode($data)
            : http_build_query($data);

        $headers = ["Content-Type: $contentType"];
        if ($bearerToken) {
            $headers[] = 'Authorization: Bearer ' . $bearerToken;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log('[FCM] cURL error: ' . $err);
            return [];
        }

        return json_decode((string)$resp, true) ?? [];
    }

    // ─────────────────────────────────────────────────────────────
    //  DB HELPERS
    // ─────────────────────────────────────────────────────────────

    private static function tableReady(): bool
    {
        if (self::$tableExists !== null) return self::$tableExists;
        self::$tableExists = (bool) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'fcm_tokens'"
        );
        return self::$tableExists;
    }

    private static function createTable(): void
    {
        Database::run("
            CREATE TABLE IF NOT EXISTS fcm_tokens (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id    BIGINT UNSIGNED NOT NULL,
                token      VARCHAR(512)    NOT NULL,
                created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_fcm_token (token),
                KEY idx_fcm_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        self::$tableExists = true;
    }

    private static function getTokensByRole(string $role): array
    {
        if (!self::tableReady()) return [];
        return array_column(Database::all(
            'SELECT DISTINCT ft.token FROM fcm_tokens ft
             JOIN users u ON u.id = ft.user_id
             WHERE u.role = ? AND ft.token IS NOT NULL',
            [$role]
        ), 'token');
    }

    private static function getTokensByUser(int $userId): array
    {
        if (!self::tableReady()) return [];
        return array_column(Database::all(
            'SELECT DISTINCT token FROM fcm_tokens WHERE user_id = ? AND token IS NOT NULL',
            [$userId]
        ), 'token');
    }
}
