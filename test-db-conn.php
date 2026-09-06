<?php
require_once __DIR__ . '/config/config.php';

try {
    $config = require __DIR__ . '/config/config.php';
    $db = $config['db'];
    
    $dsn = sprintf("%s:host=%s;port=%d;dbname=%s;charset=%s", 
        $db['driver'], 
        $db['host'], 
        $db['port'], 
        $db['database'], 
        $db['charset']
    );
    
    $pdo = new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "SUCCESS: Connected to database '{$db['database']}' on '{$db['host']}:{$db['port']}' successfully!\n";
} catch (\Exception $e) {
    echo "ERROR: Database connection failed: " . $e->getMessage() . "\n";
}
