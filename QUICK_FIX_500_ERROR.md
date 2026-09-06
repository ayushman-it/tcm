# 🔧 Quick Fix - 500 Error on Dashboard

## 🎯 Problem
Student dashboard pe course click karne par 500 error aa raha hai.

---

## ✅ Step-by-Step Fix

### **Step 1: Run Test Script**
```
Open browser:
http://localhost/tcm/tcm-2.0/test-dashboard.php
```

Yeh script exact problem batayega.

---

### **Step 2: Check Database Table**
```bash
# Open MySQL
mysql -u root

# Select database
USE tcm_db;

# Check if table exists
SHOW TABLES LIKE 'lesson_content';

# If NOT exists, create it:
SOURCE c:/xampp/htdocs/tcm/tcm-2.0/database/lesson_content.sql;
```

**OR via PHP:**
```bash
cd c:\xampp\htdocs\tcm\tcm-2.0
c:\xampp\php\php.exe check-lesson-content-table.php
```

---

### **Step 3: Verify PHP Syntax**
```bash
# Check CourseController
c:\xampp\php\php.exe -l src\Controllers\Student\CourseController.php

# Check LessonConceptsController
c:\xampp\php\php.exe -l src\Controllers\Student\LessonConceptsController.php

# Check Dashboard
c:\xampp\php\php.exe -l views\student\dashboard.php
```

All should say: **"No syntax errors detected"**

---

### **Step 4: Check Error Logs**

#### **Apache Error Log:**
```
c:\xampp\apache\logs\error.log
```
Look for recent errors (last few lines)

#### **PHP Error Log:**
Create if not exists:
```php
// In config.php, add:
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');
```

---

### **Step 5: Enable Debug Mode**

Open: `config/config.php`

Add at top:
```php
<?php
// Temporary debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
```

Now refresh dashboard - errors will show on screen.

---

### **Step 6: Test Individual Components**

#### **Test 1: Can you access student dashboard?**
```
http://localhost/tcm/tcm-2.0/student
```
✅ Should load → Dashboard code is fine
❌ Gives error → Dashboard file issue

#### **Test 2: Can you access course list?**
```
http://localhost/tcm/tcm-2.0/student/courses
```
✅ Should load → Courses route works
❌ Gives error → Route or controller issue

#### **Test 3: Test API endpoint directly:**
```
http://localhost/tcm/tcm-2.0/student/learn/1/lessons
```
Replace `1` with actual course ID

Expected: JSON response with lessons list
If error: getLessons() method has issue

---

## 🐛 Common Issues & Solutions

### **Issue 1: Class Not Found**
```
Error: Class 'TCM\Controllers\Student\LessonConceptsController' not found
```

**Solution:**
```bash
# Check file exists
dir src\Controllers\Student\LessonConceptsController.php

# Check namespace
# First line should be: namespace TCM\Controllers\Student;
```

---

### **Issue 2: Table Doesn't Exist**
```
Error: Table 'tcm_db.lesson_content' doesn't exist
```

**Solution:**
```bash
cd c:\xampp\htdocs\tcm\tcm-2.0
mysql -u root tcm_db < database\lesson_content.sql
```

---

### **Issue 3: Undefined Method**
```
Error: Call to undefined method getLessons()
```

**Solution:**
Check if method exists in CourseController:
```php
public function getLessons(array $params): void
{
    // Method body
}
```

---

### **Issue 4: Route Not Found**
```
Error: 404 Not Found
```

**Solution:**
Check `app.php` has these routes:
```php
$router->get('/student/learn/{id}/lessons', 
    ['TCM\Controllers\Student\CourseController', 'getLessons']);

$router->get('/student/lesson-concepts/{id}', 
    ['TCM\Controllers\Student\LessonConceptsController', 'getConcepts']);
```

---

### **Issue 5: JavaScript Error**
```
Error in browser console: toggleCourseTopics is not defined
```

**Solution:**
Check dashboard.php has JavaScript functions at bottom:
```javascript
function toggleCourseTopics(courseId) { ... }
function loadCourseLessons(courseId) { ... }
function toggleLessonConcepts(lessonId) { ... }
function loadLessonConcepts(lessonId) { ... }
```

---

## 🧪 Quick Tests

### **Test 1: PHP Version**
```bash
c:\xampp\php\php.exe -v
```
Should be: **PHP 8.2.x** or higher

### **Test 2: Apache Status**
```
Open: http://localhost/
```
Should show XAMPP dashboard

### **Test 3: MySQL Status**
```bash
mysql -u root -e "SELECT 1"
```
Should return: **1**

### **Test 4: Database Exists**
```bash
mysql -u root -e "SHOW DATABASES LIKE 'tcm_db'"
```
Should return: **tcm_db**

---

## 🚀 Fresh Start (Nuclear Option)

If nothing works, clean restart:

### **Step 1: Backup**
```bash
# Backup database
mysqldump -u root tcm_db > tcm_backup.sql
```

### **Step 2: Stop Services**
```
XAMPP Control Panel → Stop Apache & MySQL
```

### **Step 3: Clear Logs**
```bash
del c:\xampp\apache\logs\error.log
```

### **Step 4: Restart Services**
```
XAMPP Control Panel → Start Apache & MySQL
```

### **Step 5: Test Again**
```
http://localhost/tcm/tcm-2.0/test-dashboard.php
```

---

## 📞 Still Not Working?

### **Get More Info:**

Create this file: `debug-500.php`
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';

echo "<h2>Debug Info</h2>";
echo "<pre>";

// Test database
try {
    $db = getDbConnection();
    echo "Database: ✅ Connected\n";
    
    // Check tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . count($tables) . " found\n";
    
    // Check lesson_content
    if (in_array('lesson_content', $tables)) {
        echo "lesson_content: ✅ Exists\n";
    } else {
        echo "lesson_content: ❌ Missing\n";
    }
    
} catch (Exception $e) {
    echo "Database: ❌ " . $e->getMessage() . "\n";
}

// Test autoloader
echo "\nAutoloader: ";
if (class_exists('TCM\Controllers\Student\CourseController')) {
    echo "✅ Working\n";
} else {
    echo "❌ Failed\n";
}

// Test routes
echo "\nRoutes file: ";
if (file_exists(__DIR__ . '/app.php')) {
    echo "✅ Exists\n";
} else {
    echo "❌ Missing\n";
}

echo "</pre>";
?>
```

Then visit:
```
http://localhost/tcm/tcm-2.0/debug-500.php
```

---

## ✅ Success Checklist

- [ ] test-dashboard.php shows all ✅
- [ ] No syntax errors in any PHP file
- [ ] lesson_content table exists
- [ ] Routes are defined in app.php
- [ ] JavaScript functions present in dashboard.php
- [ ] Can access /student/courses without error
- [ ] Can access /student/learn/1 without error
- [ ] No errors in Apache log
- [ ] GEMINI_API_KEY or OPENAI_API_KEY in .env

---

## 🎉 After Fix

Once fixed:
1. Clear browser cache
2. Refresh dashboard
3. Click on a course
4. Should expand with lessons
5. Click on lesson
6. Should show "Generating AI concepts..."
7. Wait 30-60 seconds
8. Concepts should appear!

---

**Need more help? Run the test script first - it will pinpoint the exact issue!**
