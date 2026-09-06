<?php
/**
 * Cron Job: Generate Daily Tasks
 * 
 * Setup in crontab:
 * # Daily at 12:00 PM - Send notifications
 * 0 12 * * * cd /path/to/tcm-2.0 && php cron-daily-tasks.php
 * 
 * # Every Friday at 12:00 PM - Generate new tasks
 * 0 12 * * 5 cd /path/to/tcm-2.0 && php cron-daily-tasks.php
 */

require __DIR__ . '/src/bootstrap.php';

use TCM\Commands\GenerateDailyTasks;

try {
    $command = new GenerateDailyTasks();
    $command->run();
    exit(0);
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    error_log("Daily Tasks Cron Error: " . $e->getMessage());
    exit(1);
}
