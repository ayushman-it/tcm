<?php
declare(strict_types=1);
namespace TCM\Commands;

use TCM\Core\Database;
use TCM\Core\FirebaseNotification;
use TCM\Models\DailyTask;
use TCM\Models\Notification;

/**
 * Generate Daily Tasks Command
 * Run via cron: Daily at 12:00 PM for notifications
 *               Every Friday for new task generation
 */
final class GenerateDailyTasks
{
    public function run(): void
    {
        echo "🤖 Starting Daily Task Generation...\n\n";

        $today = date('l'); // Day name
        $isFriday = ($today === 'Friday');

        if ($isFriday) {
            echo "📅 Today is Friday - Generating NEW weekly tasks\n\n";
            $this->generateWeeklyTasks();
        } else {
            echo "🔔 Sending daily task notifications\n\n";
            $this->sendDailyNotifications();
        }

        echo "\n✅ Task generation complete!\n";
    }

    /**
     * Generate new tasks for all active students (Every Friday)
     */
    private function generateWeeklyTasks(): void
    {
        // Get all active students
        $students = Database::all(
            "SELECT id, name, email FROM users 
             WHERE role = 'student' AND status = 'active'"
        );

        $generated = 0;
        $failed = 0;

        foreach ($students as $student) {
            echo "  Generating for {$student['name']}... ";
            
            try {
                $tasks = DailyTask::generateForStudent((int)$student['id']);
                
                if (!empty($tasks)) {
                    $generated++;
                    echo "✓ (" . count($tasks) . " tasks)\n";
                    
                    // Send notification
                    $this->notifyNewTasks($student, $tasks);
                } else {
                    echo "⚠ No tasks generated\n";
                }
            } catch (\Exception $e) {
                $failed++;
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        }

        echo "\n📊 Summary:\n";
        echo "   ✅ Generated: {$generated} students\n";
        echo "   ❌ Failed: {$failed} students\n";
    }

    /**
     * Send notifications about today's tasks (Daily at noon)
     */
    private function sendDailyNotifications(): void
    {
        // Get all students with pending tasks for current week
        $weekStart = $this->getCurrentWeekStart();
        
        $studentsWithTasks = Database::all(
            "SELECT DISTINCT u.id, u.name, 
                    (SELECT COUNT(*) FROM daily_tasks WHERE user_id = u.id AND week_start = ? AND status = 'pending') as pending_count
             FROM users u
             JOIN daily_tasks dt ON dt.user_id = u.id
             WHERE u.role = 'student' AND u.status = 'active'
               AND dt.week_start = ?
               AND dt.status IN ('pending', 'in_progress')
             HAVING pending_count > 0",
            [$weekStart, $weekStart]
        );

        $notified = 0;

        foreach ($studentsWithTasks as $student) {
            echo "  Notifying {$student['name']}... ";
            
            try {
                $this->sendDailyReminder($student);
                $notified++;
                echo "✓\n";
            } catch (\Exception $e) {
                echo "✗ " . $e->getMessage() . "\n";
            }
        }

        echo "\n📊 Notifications sent: {$notified}\n";
    }

    /**
     * Notify student about new weekly tasks
     */
    private function notifyNewTasks(array $student, array $tasks): void
    {
        $taskCount = count($tasks);
        $title = "🎯 New Learning Tasks Generated!";
        $body = "Your personalized tasks for this week are ready. {$taskCount} activities to boost your skills!";

        // In-app notification
        Notification::ensureTable();
        Notification::forUser(
            (int)$student['id'],
            $title,
            $body,
            '🎯',
            base_url('/student')
        );

        // Push notification
        FirebaseNotification::notifyStudent(
            (int)$student['id'],
            $title,
            $body,
            ['tag' => 'daily-tasks', 'action' => 'view_tasks'],
            base_url('/student')
        );
    }

    /**
     * Send daily reminder about pending tasks
     */
    private function sendDailyReminder(array $student): void
    {
        $pendingCount = (int)$student['pending_count'];
        
        $title = "⏰ Daily Reminder";
        $body = "You have {$pendingCount} task" . ($pendingCount > 1 ? 's' : '') . " waiting for you today. Let's make progress! 💪";

        // In-app notification
        Notification::ensureTable();
        Notification::forUser(
            (int)$student['id'],
            $title,
            $body,
            '⏰',
            base_url('/student')
        );

        // Push notification
        FirebaseNotification::notifyStudent(
            (int)$student['id'],
            $title,
            $body,
            ['tag' => 'task-reminder'],
            base_url('/student')
        );
    }

    private function getCurrentWeekStart(): string
    {
        $today = new \DateTime();
        $dayOfWeek = (int)$today->format('N');
        
        if ($dayOfWeek >= 5) {
            $daysToFriday = $dayOfWeek - 5;
            $friday = $today->modify("-{$daysToFriday} days");
        } else {
            $daysToLastFriday = $dayOfWeek + 2;
            $friday = $today->modify("-{$daysToLastFriday} days");
        }

        return $friday->format('Y-m-d');
    }
}
