# 🐛 Debug Guide - Troubleshooting Issues

## Issue 1: Expandable Lesson "Error Loading Content"

### Debug Steps:

1. **Open Browser Console** (F12 → Console tab)

2. **Navigate to course page**: `/student/learn/{courseId}`

3. **Click expand button** on any lesson

4. **Check console logs**:
   - Should see: `[Lesson] Loading from: /student/lessons/{id}/content?mode=basic`
   - Should see: `[Lesson] Response status: 200`
   - Should see: `[Lesson] Response data: {success: true, ...}`

### Common Errors:

**Error: 404 Not Found**
- Route not registered
- Check `app.php` has: `$router->get('/student/lessons/{lessonId}/content', ...)`

**Error: 500 Internal Server Error**
- Check PHP error logs
- Look for: `[Lesson Content] Error:` in error log
- Database connection issue?
- OpenRouter API error?

**Error: "Lesson not found"**
- Lesson ID doesn't exist in database
- Check: `SELECT * FROM course_lessons WHERE id = {lessonId}`

**Error: "Failed to parse AI response"**
- AI returned invalid JSON
- Check fallback content is being used
- OpenRouter API key missing?

### Manual Test:

**Test API directly**:
```
URL: http://yoursite.com/student/lessons/1/content?mode=basic
Expected: JSON response with lesson content
```

**Check PHP Error Log**:
```
Location: C:\xampp\php\logs\php_error_log (Windows)
Look for: [Lesson Content] errors
```

**Check Database**:
```sql
-- Check if lessons exist
SELECT l.id, l.title, m.title as module, c.title as course
FROM course_lessons l
JOIN course_modules m ON m.id = l.module_id  
JOIN courses c ON c.id = m.course_id
LIMIT 10;
```

---

## Issue 2: Scheduled Classes Not Showing

### Debug Steps:

1. **Check if live_class_links table exists**:
```sql
SHOW TABLES LIKE 'live_class_links';
```

2. **Check if any scheduled classes exist**:
```sql
SELECT * FROM live_class_links 
ORDER BY created_at DESC 
LIMIT 10;
```

3. **Check student's enrollment**:
```sql
-- For 'course' target
SELECT * FROM enrollments WHERE user_id = {studentId};

-- For 'program' target  
SELECT * FROM program_enrollments WHERE user_id = {studentId};
```

4. **Check PHP error logs**:
```
Look for: [Live Classes] Error:
Look for: [Live Classes] Found X links
```

### Common Issues:

**No classes showing - Possible reasons**:

1. **No scheduled classes created**
   - Admin hasn't created any
   - Go to: `/admin/live-class` and create one

2. **Target mismatch**
   - Class targeted to "course" but student not enrolled
   - Class targeted to "program" but student not enrolled in that program
   - Class targeted to "specific" but student not in the list

3. **Time filter issue**
   - Scheduled time is > 7 days in future
   - Scheduled time passed by > 2 hours
   - Check query conditions

4. **Database query error**
   - Check error logs for SQL errors
   - Check if columns exist (scheduled_at, target, etc.)

### SQL Query Explanation:

The query shows classes if:
```sql
-- Scheduled in future (next 7 days)
scheduled_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
AND scheduled_at <= DATE_ADD(NOW(), INTERVAL 7 DAY)

-- OR created recently (last 24h) with no schedule
(scheduled_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
```

### Manual Test Query:

Run this as the student to see what they should see:

```sql
SET @user_id = 1; -- Replace with actual student ID

-- Get enrolled courses
SELECT @course_ids := GROUP_CONCAT(course_id) 
FROM enrollments 
WHERE user_id = @user_id;

-- Get enrolled programs
SELECT @program_ids := GROUP_CONCAT(program_id) 
FROM program_enrollments 
WHERE user_id = @user_id;

-- Check what classes student should see
SELECT 
    id, 
    title, 
    target, 
    target_id,
    scheduled_at,
    created_at,
    CASE 
        WHEN target = 'all' THEN 'MATCH: All students'
        WHEN target = 'course' AND FIND_IN_SET(target_id, @course_ids) THEN 'MATCH: Enrolled course'
        WHEN target = 'program' AND FIND_IN_SET(target_id, @program_ids) THEN 'MATCH: Enrolled program'
        ELSE 'NO MATCH'
    END as match_reason
FROM live_class_links
WHERE (
    target = 'all'
    OR (target = 'course' AND FIND_IN_SET(target_id, @course_ids))
    OR (target = 'program' AND FIND_IN_SET(target_id, @program_ids))
)
AND (
    (scheduled_at IS NOT NULL 
     AND scheduled_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR) 
     AND scheduled_at <= DATE_ADD(NOW(), INTERVAL 7 DAY))
    OR (scheduled_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
)
ORDER BY scheduled_at ASC, created_at DESC;
```

---

## Quick Fixes

### Fix 1: Create Test Scheduled Class

```sql
-- Insert test class for all students
INSERT INTO live_class_links (
    title, 
    meeting_url, 
    description,
    target,
    scheduled_at,
    sent_by
) VALUES (
    'Test Live Class',
    'https://meet.google.com/test',
    'This is a test class',
    'all',
    DATE_ADD(NOW(), INTERVAL 1 HOUR), -- 1 hour from now
    1 -- Admin user ID
);
```

### Fix 2: Check OpenRouter API Key

```php
// In CourseController.php
$apiKey = config('openrouter.api_key', '');
error_log('API Key present: ' . (!empty($apiKey) ? 'YES' : 'NO'));
```

Check `.env` file:
```
OPENROUTER_API_KEY=your-openrouter-api-key-here
```

### Fix 3: Test Fallback Content

If AI fails, fallback should work. Test by temporarily removing API key:

```php
// Temporarily disable AI
private function generateLessonContent(array $lesson, string $mode): array
{
    // Force fallback for testing
    return $this->getFallbackContent($lesson, $mode);
}
```

---

## Logging Locations

### PHP Error Log:
- **XAMPP**: `C:\xampp\php\logs\php_error_log`
- **Linux**: `/var/log/php_errors.log`

### Check for these messages:
```
[Lesson Content] Error:
[Lesson Content] Trace:
[Lesson Content] Lesson not found:
[Live Classes] Error:
[Live Classes] Found X links
```

---

## Browser Console Commands

### Test AJAX call directly:
```javascript
// Test lesson content API
fetch('/student/lessons/1/content?mode=basic')
  .then(r => r.json())
  .then(d => console.log('Response:', d))
  .catch(e => console.error('Error:', e));
```

### Check if jQuery/fetch available:
```javascript
console.log('Fetch available:', typeof fetch);
console.log('Base URL:', '<?= base_url() ?>');
```

---

## Verification Checklist

### Expandable Lessons:
- [ ] Route exists in `app.php`
- [ ] Controller method exists
- [ ] Database has lessons
- [ ] API key configured (or fallback works)
- [ ] JavaScript has no syntax errors
- [ ] Console shows proper logs
- [ ] Response is valid JSON

### Scheduled Classes:
- [ ] Table `live_class_links` exists
- [ ] At least one class created
- [ ] Class target matches student (all/course/program)
- [ ] Class is within time window
- [ ] Student has enrollments
- [ ] No SQL errors in logs
- [ ] Dashboard code has no PHP errors

---

## Still Not Working?

### Enable Debug Mode:

**In `.env`**:
```
APP_DEBUG=true
```

**Add more logging**:
```php
// In dashboard.php, after query
error_log('[Live Classes] SQL: ' . $sql);
error_log('[Live Classes] Params: ' . print_r($params, true));
error_log('[Live Classes] Results: ' . print_r($liveLinks, true));

// In CourseController.php
error_log('[Lesson] Lesson data: ' . print_r($lesson, true));
error_log('[Lesson] Mode: ' . $mode);
error_log('[Lesson] API Key exists: ' . (!empty($apiKey) ? 'yes' : 'no'));
```

### Contact for Help:

Provide these details:
1. Browser console errors (screenshot)
2. PHP error log (relevant lines)
3. SQL query results
4. Which exact step is failing

---

**Remember**: 
- Clear browser cache after code changes
- Restart Apache after PHP changes
- Check both browser console AND PHP error log
