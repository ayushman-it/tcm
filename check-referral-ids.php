<?php
require_once __DIR__ . '/app.php';

// Check students without referral_id
$studentsWithoutReferral = \TCM\Core\Database::all(
    "SELECT id, name, email, student_id, referral_id 
     FROM users 
     WHERE role = 'student' 
     ORDER BY id"
);

echo "=== Checking Referral IDs ===\n\n";

$withReferral = 0;
$withoutReferral = 0;

foreach ($studentsWithoutReferral as $student) {
    if (empty($student['referral_id'])) {
        echo "❌ Student ID {$student['id']} ({$student['name']}) - NO REFERRAL ID\n";
        $withoutReferral++;
    } else {
        echo "✅ Student ID {$student['id']} ({$student['name']}) - Referral: {$student['referral_id']}\n";
        $withReferral++;
    }
}

echo "\n=== Summary ===\n";
echo "Students WITH referral ID: $withReferral\n";
echo "Students WITHOUT referral ID: $withoutReferral\n";

if ($withoutReferral > 0) {
    echo "\n⚠️ Problem found! $withoutReferral students need referral IDs.\n";
    echo "Run the migration script to fix this.\n";
} else {
    echo "\n✅ All students have referral IDs!\n";
}
