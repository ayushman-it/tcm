# 📚 Course Notes System - Complete Setup Guide

## ✨ Features Implemented

### 1. **W3Schools Style Reading Interface**
- Clean, distraction-free reading experience
- Table of contents sidebar
- Previous/Next navigation
- Progress tracking
- Mobile responsive

### 2. **Access Control**
- Only enrolled students can access
- Duration-based access (expires after course duration)
- Automatic access revocation

### 3. **Progress Tracking**
- Auto-mark chapters as read
- Overall progress percentage
- Per-course reading stats

---

## 🗄️ Database Setup

### Run This SQL:

```sql
-- ════════════════════════════════════════════════════════════
-- Course Notes System Tables
-- ════════════════════════════════════════════════════════════

-- 1. Course Notes/Books Table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Student Reading Progress
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

-- 3. Add duration to enrollments (if not exists)
ALTER TABLE enrollments 
ADD COLUMN IF NOT EXISTS duration_days INT DEFAULT NULL COMMENT 'Course access duration in days' AFTER status,
ADD COLUMN IF NOT EXISTS expires_at DATE DEFAULT NULL COMMENT 'Access expiry date' AFTER duration_days,
ADD COLUMN IF NOT EXISTS enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Enrollment date' AFTER expires_at;
```

---

## 📂 Files Created

```
src/Models/CourseNote.php              - Notes model
src/Controllers/Student/NoteController.php - Notes controller
views/student/notes/index.php          - Table of contents
views/student/notes/show.php           - Reading view
database/course_notes.sql              - Database migration
```

---

## 🚀 Routes Added

```php
// In app.php (already added)
GET  /student/notes/{id}         - Course notes index
GET  /student/notes/{id}/{slug}  - Read specific note
```

---

## 📝 How to Add Notes (Admin)

### Method 1: Direct Database Insert

```sql
INSERT INTO course_notes 
(course_id, title, slug, content, excerpt, order_index, estimated_reading_time) 
VALUES 
(1, 'Introduction to JavaScript', 'introduction-to-javascript', 
'<h1>Introduction to JavaScript</h1>
<p>JavaScript is a programming language...</p>
<h2>What You''ll Learn</h2>
<ul>
  <li>Variables and Data Types</li>
  <li>Functions</li>
  <li>DOM Manipulation</li>
</ul>', 
'Learn the basics of JavaScript programming',
1, 
15);
```

### Method 2: Bulk Import (Sample Content)

```sql
-- Chapter 1
INSERT INTO course_notes (course_id, title, slug, content, order_index, estimated_reading_time) VALUES
(1, 'Chapter 1: Getting Started', 'getting-started', 
'<h1>Getting Started with JavaScript</h1>
<p>Welcome to your JavaScript journey! In this chapter, we''ll cover the basics.</p>
<h2>What is JavaScript?</h2>
<p>JavaScript is a versatile programming language used to create interactive websites.</p>
<pre><code>console.log("Hello, World!");</code></pre>', 
1, 10);

-- Chapter 2
INSERT INTO course_notes (course_id, title, slug, content, order_index, estimated_reading_time) VALUES
(1, 'Chapter 2: Variables', 'variables', 
'<h1>Variables in JavaScript</h1>
<p>Variables are containers for storing data values.</p>
<h2>Declaring Variables</h2>
<pre><code>let name = "John";
const age = 30;
var city = "Mumbai";</code></pre>', 
2, 15);

-- Chapter 3
INSERT INTO course_notes (course_id, title, slug, content, order_index, estimated_reading_time) VALUES
(1, 'Chapter 3: Functions', 'functions', 
'<h1>Functions</h1>
<p>Functions are reusable blocks of code.</p>
<pre><code>function greet(name) {
  return "Hello, " + name;
}
console.log(greet("World"));</code></pre>', 
3, 20);
```

---

## 🔒 Access Control System

### How It Works:

```
1. Student enrolls in course
   ↓
2. System sets enrollment.expires_at 
   (based on course duration or enrollment + duration_days)
   ↓
3. Student accesses /student/notes/{courseId}
   ↓
4. System checks:
   ✓ Is enrolled?
   ✓ Is still within duration?
   ✓ Course is active?
   ↓
5. If YES → Show notes
   If NO → Redirect with error message
```

### Setting Course Duration:

```sql
-- Set 90 days access for a student
UPDATE enrollments 
SET duration_days = 90,
    expires_at = DATE_ADD(enrolled_at, INTERVAL 90 DAY)
WHERE user_id = 1 AND course_id = 1;

-- Extend access by 30 days
UPDATE enrollments 
SET expires_at = DATE_ADD(expires_at, INTERVAL 30 DAY)
WHERE user_id = 1 AND course_id = 1;

-- Unlimited access (never expires)
UPDATE enrollments 
SET duration_days = NULL,
    expires_at = NULL
WHERE user_id = 1 AND course_id = 1;
```

---

## 🎨 UI Features

### Table of Contents:
- Numbered chapters
- Reading time estimates
- Progress indicators
- Clickable navigation

### Reading View:
- Clean typography
- Syntax highlighting for code
- Prev/Next navigation
- Progress sidebar
- Mobile responsive

---

## 📊 Example Content Structure

```
Course: Full Stack Development
├─ Chapter 1: Introduction (10 min)
├─ Chapter 2: HTML Basics (15 min)
├─ Chapter 3: CSS Styling (20 min)
├─ Chapter 4: JavaScript Fundamentals (25 min)
│  ├─ 4.1 Variables (nested)
│  └─ 4.2 Functions (nested)
├─ Chapter 5: React.js (30 min)
└─ Chapter 6: Final Project (40 min)
```

---

## 🧪 Testing

### Test 1: Create Sample Notes

```bash
mysql -u root -p tcm < database/course_notes.sql
```

### Test 2: Enroll Student

```sql
-- Enroll student with 90-day access
INSERT INTO enrollments (user_id, course_id, status, duration_days, expires_at, enrolled_at)
VALUES (1, 1, 'active', 90, DATE_ADD(NOW(), INTERVAL 90 DAY), NOW());
```

### Test 3: Access Notes

```
1. Login as student
2. Go to: /student/notes/1
3. Should see table of contents
4. Click any chapter
5. Should see reading view
```

---

## 🔗 Integration with Courses

### Add "View Notes" button to course page:

```php
<!-- In views/student/courses/show.php -->
<?php if ($enrollment && $enrollment['status'] === 'active'): ?>
<a href="<?= base_url('/student/notes/' . $course['id']) ?>" 
   class="tcm-btn primary">
    <i class="bi bi-book"></i> Read Course Notes
</a>
<?php endif; ?>
```

---

## 📈 Analytics

### Track Reading Progress:

```sql
-- Most read chapters
SELECT cn.title, COUNT(*) as reads
FROM student_note_progress snp
JOIN course_notes cn ON cn.id = snp.note_id
GROUP BY cn.id
ORDER BY reads DESC
LIMIT 10;

-- Students with 100% completion
SELECT u.name, c.title
FROM users u
JOIN enrollments e ON e.user_id = u.id
JOIN courses c ON c.id = e.course_id
WHERE e.course_id = 1
  AND (SELECT COUNT(*) FROM student_note_progress snp 
       JOIN course_notes cn ON cn.id = snp.note_id 
       WHERE snp.user_id = u.id AND cn.course_id = 1)
    = (SELECT COUNT(*) FROM course_notes WHERE course_id = 1);
```

---

## 🎯 Quick Start Summary

```bash
# 1. Run database migration
mysql -u root -p tcm < database/course_notes.sql

# 2. Add sample notes (via SQL or admin panel)
# See "How to Add Notes" section above

# 3. Set course duration for enrollments
UPDATE enrollments SET duration_days = 90, 
expires_at = DATE_ADD(enrolled_at, INTERVAL 90 DAY);

# 4. Test access
# Login as student → Go to /student/notes/1
```

---

## ✅ Features Summary

- ✅ W3Schools style reading interface
- ✅ Duration-based access control
- ✅ Progress tracking
- ✅ Table of contents navigation
- ✅ Mobile responsive design
- ✅ Code syntax highlighting ready
- ✅ Prev/Next chapter navigation
- ✅ Reading time estimates
- ✅ Access expiry notifications

**System is production-ready!** 🚀
