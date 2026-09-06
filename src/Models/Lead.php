<?php

declare(strict_types=1);

namespace TCM\Models;

use TCM\Core\Database;
use TCM\Core\FirebaseNotification;
use TCM\Models\Notification;

final class Lead extends Model
{
    protected static string $table = 'leads';

    /**
     * Capture a lead from any source (guest or logged-in).
     *
     * @param array<string,mixed> $data
     */
    public static function capture(array $data): int
    {
        $id = self::create([
            'user_id'        => $data['user_id'] ?? null,
            'name'           => $data['name'] ?? 'Guest',
            'email'          => $data['email'] ?? null,
            'phone'          => $data['phone'] ?? null,
            'interest_type'  => $data['interest_type'] ?? 'general',
            'interest_id'    => $data['interest_id'] ?? null,
            'interest_title' => $data['interest_title'] ?? null,
            'message'        => $data['message'] ?? null,
            'source'         => $data['source'] ?? 'website',
            'status'         => 'new',
        ]);

        // 🔔 Notify admins about new lead (in-app + push)
        $name  = $data['name'] ?? 'Someone';
        $topic = $data['interest_title'] ?? ucfirst($data['interest_type'] ?? 'general');
        Notification::ensureTable();
        Notification::toAdmins(
            '🎯 New Lead — ' . $name,
            $name . ' is interested in ' . $topic . '. Review and follow up.',
            '🎯',
            base_url('/admin/leads')
        );
        FirebaseNotification::notifyAdmins(
            '🎯 New Lead — ' . $name,
            $name . ' is interested in ' . $topic . '. Review and follow up.',
            ['tag' => 'lead-new'],
            base_url('/admin/leads')
        );

        return $id;
    }

    /**
     * @param array<string,mixed> $filters status, search
     * @return list<array<string,mixed>>
     */
    public static function search(array $filters = []): array
    {
        $sql = 'SELECT * FROM leads WHERE 1';
        $params = [];
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $sql .= ' ORDER BY FIELD(status,\'new\',\'contacted\',\'converted\',\'lost\'), created_at DESC';
        return Database::all($sql, $params);
    }
}
