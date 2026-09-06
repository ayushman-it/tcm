-- ════════════════════════════════════════════════════════════
-- TCM 2.0: Course Notes System (W3Schools Style)
-- ════════════════════════════════════════════════════════════

-- Course Notes/Books Table
CREATE TABLE IF NOT EXISTS course_notes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    course_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL COMMENT 'Chapter/Section title',
    slug VARCHAR(200) NOT NULL COMMENT 'URL-friendly slug',
    content LONGTEXT NOT NULL COMMENT 'HTML content of the note',
    excerpt TEXT DEFAULT NULL COMMENT 'Short description',
    order_index INT DEFAULT 0 COMMENT 'Display order',
    parent_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'For nested chapters',
    is_published TINYINT(1) DEFAULT 1,
    estimated_reading_time INT DEFAULT 10 COMMENT 'minutes',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_course_slug (course_id, slug),
    KEY idx_course (course_id),
    KEY idx_parent (parent_id),
    KEY idx_order (order_index),
    CONSTRAINT fk_cn_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE,
    CONSTRAINT fk_cn_parent FOREIGN KEY (parent_id) REFERENCES course_notes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Course reading materials and notes (W3Schools style)';

-- Student Notes Progress (track what they've read)
CREATE TABLE IF NOT EXISTS student_note_progress (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    note_id BIGINT UNSIGNED NOT NULL,
    read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_user_note (user_id, note_id),
    KEY idx_user (user_id),
    KEY idx_note (note_id),
    CONSTRAINT fk_snp_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_snp_note FOREIGN KEY (note_id) REFERENCES course_notes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add duration field to enrollments (if not exists)
ALTER TABLE enrollments 
ADD COLUMN IF NOT EXISTS duration_days INT DEFAULT NULL COMMENT 'Course access duration in days' AFTER status,
ADD COLUMN IF NOT EXISTS expires_at DATE DEFAULT NULL COMMENT 'Access expiry date' AFTER duration_days,
ADD COLUMN IF NOT EXISTS enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Enrollment date' AFTER expires_at;

-- Sample data for testing
INSERT INTO course_notes (course_id, title, slug, content, order_index, estimated_reading_time) 
SELECT 
    id as course_id,
    CONCAT('Introduction to ', title) as title,
    'introduction' as slug,
    CONCAT('<h1>Welcome to ', title, '</h1><p>This is a comprehensive guide to learning ', title, '.</p>') as content,
    1 as order_index,
    15 as estimated_reading_time
FROM courses 
WHERE id IN (SELECT MIN(id) FROM courses)
LIMIT 3;
