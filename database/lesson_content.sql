-- =====================================================================
-- AI-Generated Lesson Content System
-- Stores detailed content, examples, and exercises for each lesson
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Detailed lesson content with AI-generated sections
CREATE TABLE IF NOT EXISTS lesson_content (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    lesson_id        BIGINT UNSIGNED NOT NULL,
    
    -- Overview Section
    overview_hi      TEXT            DEFAULT NULL,  -- Hindi overview
    overview_en      TEXT            DEFAULT NULL,  -- English overview
    
    -- Key Concepts (JSON array of concepts with explanations)
    key_concepts     JSON            DEFAULT NULL,
    
    -- Detailed Explanation
    explanation_hi   LONGTEXT        DEFAULT NULL,
    explanation_en   LONGTEXT        DEFAULT NULL,
    
    -- Code Examples (JSON array of examples)
    code_examples    JSON            DEFAULT NULL,
    
    -- Practice Exercises (JSON array)
    exercises        JSON            DEFAULT NULL,
    
    -- Additional Resources
    resources        JSON            DEFAULT NULL,
    
    -- Metadata
    difficulty_level ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
    estimated_time   INT             DEFAULT 30,    -- minutes
    language         VARCHAR(20)     NOT NULL DEFAULT 'hi+en',
    
    -- AI Generation Info
    ai_generated     TINYINT(1)      NOT NULL DEFAULT 1,
    ai_model         VARCHAR(50)     DEFAULT 'gpt-4o',
    generated_at     DATETIME        DEFAULT NULL,
    reviewed_by      BIGINT UNSIGNED DEFAULT NULL,
    status           ENUM('draft','reviewed','published') NOT NULL DEFAULT 'draft',
    
    created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY uniq_lesson_content (lesson_id),
    KEY idx_content_status (status),
    CONSTRAINT fk_content_lesson FOREIGN KEY (lesson_id) REFERENCES course_lessons (id) ON DELETE CASCADE,
    CONSTRAINT fk_content_reviewer FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student progress on exercises
CREATE TABLE IF NOT EXISTS exercise_submissions (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id          BIGINT UNSIGNED NOT NULL,
    lesson_id        BIGINT UNSIGNED NOT NULL,
    exercise_index   INT             NOT NULL,  -- which exercise in the array
    code             TEXT            DEFAULT NULL,
    status           ENUM('pending','correct','incorrect','review') NOT NULL DEFAULT 'pending',
    feedback         TEXT            DEFAULT NULL,  -- AI or instructor feedback
    attempts         INT             NOT NULL DEFAULT 0,
    submitted_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_submission_user (user_id),
    KEY idx_submission_lesson (lesson_id),
    CONSTRAINT fk_submission_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_submission_lesson FOREIGN KEY (lesson_id) REFERENCES course_lessons (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz questions for each lesson (optional)
CREATE TABLE IF NOT EXISTS lesson_quiz_questions (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    lesson_id        BIGINT UNSIGNED NOT NULL,
    question_text    TEXT            NOT NULL,
    question_type    ENUM('mcq','code','true_false') NOT NULL DEFAULT 'mcq',
    options          JSON            DEFAULT NULL,  -- for MCQ
    correct_answer   TEXT            NOT NULL,
    explanation      TEXT            DEFAULT NULL,
    points           INT             NOT NULL DEFAULT 10,
    position         INT             NOT NULL DEFAULT 0,
    
    PRIMARY KEY (id),
    KEY idx_quiz_lesson (lesson_id),
    CONSTRAINT fk_quiz_lesson FOREIGN KEY (lesson_id) REFERENCES course_lessons (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student quiz attempts
CREATE TABLE IF NOT EXISTS lesson_quiz_attempts (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id          BIGINT UNSIGNED NOT NULL,
    lesson_id        BIGINT UNSIGNED NOT NULL,
    score            INT             NOT NULL DEFAULT 0,
    max_score        INT             NOT NULL DEFAULT 0,
    answers          JSON            DEFAULT NULL,
    passed           TINYINT(1)      NOT NULL DEFAULT 0,
    attempted_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_attempt_user (user_id),
    KEY idx_attempt_lesson (lesson_id),
    CONSTRAINT fk_attempt_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_attempt_lesson FOREIGN KEY (lesson_id) REFERENCES course_lessons (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
