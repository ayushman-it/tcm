<?php
// Direct database fix for referral IDs
$host = 'localhost';
$db   = 'tcm_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Checking Referral IDs ===\n\n";
    
    // Get all students
    $stmt = $pdo->query("SELECT id, name, email, student_id, referral_id FROM users WHERE role = 'student' ORDER BY id");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $withReferral = 0;
    $withoutReferral = 0;
    $toFix = [];
    
    foreach ($students as $student) {
        if (empty($student['referral_id'])) {
            echo "❌ Student ID {$student['id']} ({$student['name']}) - NO REFERRAL ID\n";
            $withoutReferral++;
            $toFix[] = $student;
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
        echo "\nGenerating referral IDs...\n\n";
        
        // Fix each student
        $fixed = 0;
        foreach ($toFix as $student) {
            // Generate unique referral code
            do {
                $code = 'REF-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referral_id = ?");
                $checkStmt->execute([$code]);
                $exists = $checkStmt->fetchColumn();
            } while ($exists > 0);
            
            // Update student
            $updateStmt = $pdo->prepare("UPDATE users SET referral_id = ? WHERE id = ?");
            $updateStmt->execute([$code, $student['id']]);
            
            echo "✅ Fixed Student ID {$student['id']} ({$student['name']}) → $code\n";
            $fixed++;
        }
        
        echo "\n✅ Fixed $fixed students!\n";
    } else {
        echo "\n✅ All students have referral IDs!\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
