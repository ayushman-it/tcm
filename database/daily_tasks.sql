-- ════════════════════════════════════════════════════════════
-- TCM 2.0: AI Daily Tasks System
-- Database Migration for Task Generation Feature
-- ════════════════════════════════════════════════════════════

-- Create daily_tasks table
CREATE TABLE IF NOT EXISTS daily_tasks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'Student who owns this task',
    title VARCHAR(200) NOT NULL COMMENT 'Task title',
    description TEXT NOT NULL COMMENT 'Detailed task description',
    course_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'Related course (if any)',
    course_title VARCHAR(200) DEFAULT NULL COMMENT 'Course name for display',
    difficulty ENUM('Easy','Medium','Hard') DEFAULT 'Medium' COMMENT 'Task difficulty level',
    estimated_time INT DEFAULT 30 COMMENT 'Estimated completion time in minutes',
    tags JSON DEFAULT NULL COMMENT 'Task tags/categories',
    motivation VARCHAR(255) DEFAULT NULL COMMENT 'Motivational message for student',
    status ENUM('pending','in_progress','completed','skipped') DEFAULT 'pending' COMMENT 'Task status',
    completed_at DATETIME DEFAULT NULL COMMENT 'When task was completed',
    week_start DATE NOT NULL COMMENT 'Friday of the week (tasks reset weekly)',
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When task was AI-generated',
    notified_at DATETIME DEFAULT NULL COMMENT 'When notification was sent',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_date (user_id, week_start, status) COMMENT 'Fast lookup of user tasks by week',
    KEY idx_course (course_id) COMMENT 'Find tasks by course',
    KEY idx_status (status) COMMENT 'Filter by status',
    CONSTRAINT fk_dt_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='AI-generated personalized learning tasks for students';

-- Insert sample task (for testing)
INSERT INTO daily_tasks 
(user_id, title, description, difficulty, estimated_time, motivation, week_start, status) 
VALUES 
(1, 
 '📚 Complete Introduction to Variables', 
 'Learn about variables, data types, and how to declare them in JavaScript. Practice with 5 examples.',
 'Easy',
 30,
 '💪 Great start! Variables are the foundation of programming!',
 DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) - 4 DAY),
 'pending'
);

-- ════════════════════════════════════════════════════════════
-- Verification Queries
-- ════════════════════════════════════════════════════════════

-- Check if table was created successfully
SELECT 
    'Table created successfully!' as message,
    COUNT(*) as sample_tasks
FROM daily_tasks;

-- Show table structure
DESCRIBE daily_tasks;
