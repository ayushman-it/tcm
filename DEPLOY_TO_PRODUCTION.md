# 🚀 Quick Deploy - thecodemunk.in

## 📦 Files Package for Upload

Maine jo changes kiye hain, unhe production pe upload karna hai.

---

## 📁 Changed Files List:

### **1. Controllers (NEW/MODIFIED)**
```
✅ src/Controllers/Student/CourseController.php (MODIFIED)
✅ src/Controllers/Student/LessonConceptsController.php (NEW)
```

### **2. Views (MODIFIED)**
```
✅ views/student/dashboard.php (MODIFIED)
```

### **3. Routes (MODIFIED)**
```
✅ app.php (MODIFIED)
```

### **4. Database (IF NOT EXISTS)**
```
⚠️ database/lesson_content.sql (CREATE TABLE)
```

### **5. Environment (UPDATE)**
```
⚠️ .env (ADD GEMINI_API_KEY)
```

---

## 🎯 Step-by-Step Deployment

### **Step 1: Prepare Files Locally**

Create a deployment package:
```bash
# Windows (your local)
cd c:\xampp\htdocs\tcm\tcm-2.0

# Create deployment folder
mkdir deploy_package

# Copy files
copy src\Controllers\Student\CourseController.php deploy_package\
copy src\Controllers\Student\LessonConceptsController.php deploy_package\
copy views\student\dashboard.php deploy_package\
copy app.php deploy_package\
copy database\lesson_content.sql deploy_package\
copy prod-debug.php deploy_package\
```

---

### **Step 2: Upload via FileZilla/cPanel**

**Connect to thecodemunk.in:**
- Host: thecodemunk.in
- Username: [your FTP username]
- Password: [your FTP password]
- Port: 21 (FTP) or 22 (SFTP)

**Upload files to:**
```
/public_html/tcm-2.0/src/Controllers/Student/CourseController.php
/public_html/tcm-2.0/src/Controllers/Student/LessonConceptsController.php
/public_html/tcm-2.0/views/student/dashboard.php
/public_html/tcm-2.0/app.php
/public_html/tcm-2.0/prod-debug.php
```

---

### **Step 3: Database Update (via phpMyAdmin)**

**Open:** https://thecodemunk.in/phpmyadmin

**Login and:**
1. Select database: `tcm_db` (or your database name)
2. Click on "SQL" tab
3. Check if table exists:
   ```sql
   SHOW TABLES LIKE 'lesson_content';
   ```
4. If NO results, run:
   ```sql
   -- Copy entire content from database/lesson_content.sql
   -- Paste here and click "Go"
   ```

---

### **Step 4: Update .env File**

**Option A: Via cPanel File Manager**
1. Go to File Manager
2. Navigate to `/public_html/tcm-2.0`
3. Right-click `.env` → Edit
4. Add this line:
   ```env
   GEMINI_API_KEY=AIzaSyBDcFdVwbhai...
   ```
5. Save

**Option B: Via FTP**
1. Download `.env` file
2. Add `GEMINI_API_KEY=...`
3. Upload back

---

### **Step 5: Set Permissions**

**Via cPanel File Manager:**

Right-click each file → Change Permissions:
```
Files: 644
  - CourseController.php
  - LessonConceptsController.php
  - dashboard.php
  - app.php

Directories: 755
  - src/Controllers/Student/
  - views/student/
```

---

### **Step 6: Test Deployment**

#### **Test 1: Debug Script**
```
https://thecodemunk.in/prod-debug.php
```

**Login as admin first!**

Should show all ✅ green checks.

#### **Test 2: Dashboard**
```
https://thecodemunk.in/student
```

Should load without errors.

#### **Test 3: Course Expansion**
1. Login as student
2. Go to dashboard
3. Click any enrolled course
4. Should expand ▼

#### **Test 4: Lesson Concepts**
1. Click any lesson
2. Should show "Generating..."
3. Wait 30-60 seconds
4. Concepts should appear!

---

## 🐛 If Something Goes Wrong

### **Error 1: White Screen / 500 Error**

**Check error logs:**
- cPanel → Metrics → Errors
- OR `/public_html/tcm-2.0/storage/logs/error.log`

**Common fixes:**
```
1. Clear cache (cPanel → PHP → OPcache Reset)
2. Check file permissions (all 644)
3. Verify .env file syntax
4. Check database connection
```

---

### **Error 2: Class Not Found**

**Solution:**
```
1. Verify file uploaded correctly
2. Check file names (case-sensitive!)
3. Clear OPcache
4. Check namespace in files
```

---

### **Error 3: Database Table Missing**

**Run this in phpMyAdmin:**
```sql
CREATE TABLE IF NOT EXISTS lesson_content (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    lesson_id BIGINT UNSIGNED NOT NULL,
    overview_hi TEXT,
    overview_en TEXT,
    key_concepts JSON,
    explanation_hi LONGTEXT,
    explanation_en LONGTEXT,
    code_examples JSON,
    exercises JSON,
    resources JSON,
    estimated_time INT DEFAULT 30,
    language VARCHAR(20) DEFAULT 'hi+en',
    ai_generated TINYINT(1) DEFAULT 1,
    ai_model VARCHAR(50) DEFAULT 'gemini-1.5-pro',
    generated_at DATETIME,
    status ENUM('draft','published') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY (lesson_id),
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);
```

---

## 🔄 Quick Rollback (If Needed)

**If something breaks:**

1. **Restore Files:**
   - Upload old versions from backup
   - CourseController.php (backup)
   - dashboard.php (backup)
   - app.php (backup)

2. **Delete New Files:**
   - Delete LessonConceptsController.php
   
3. **Restore Database:**
   ```sql
   DROP TABLE lesson_content;
   ```

4. **Clear Cache:**
   - cPanel → PHP → OPcache Reset

---

## ✅ Success Checklist

After deployment:

- [ ] prod-debug.php shows all green ✅
- [ ] Dashboard loads normally
- [ ] Can see enrolled courses
- [ ] Courses expand when clicked
- [ ] Lessons list appears
- [ ] Can click on lesson
- [ ] "Generating..." message shows
- [ ] Concepts appear after 30-60s
- [ ] No errors in logs
- [ ] Feature works for test student

---

## 📊 Verify Database

**Run in phpMyAdmin:**
```sql
-- Check table exists
SHOW TABLES LIKE 'lesson_content';

-- Check structure
DESCRIBE lesson_content;

-- Check if any content generated
SELECT COUNT(*) FROM lesson_content;
```

---

## 🎉 Post-Deployment

**Once live:**

1. **Test with real student account**
2. **Monitor for 30 minutes**
3. **Check error logs**
4. **Ask 2-3 students for feedback**
5. **Delete prod-debug.php** (security)

---

## 📞 Quick Support

**Error Logs Location:**
```
cPanel → Metrics → Errors
OR
/public_html/tcm-2.0/storage/logs/error.log
```

**Clear Cache:**
```
cPanel → Software → PHP → OPcache → Reset
```

**Check Database:**
```
cPanel → Databases → phpMyAdmin
```

---

## 🚀 Ready to Deploy!

**Estimated Time:** 15-20 minutes

**Deployment Order:**
1. Upload files (5 min)
2. Update database (3 min)
3. Update .env (2 min)
4. Set permissions (2 min)
5. Test (5 min)
6. Monitor (ongoing)

**Go ahead! 🎯**

Agar koi problem aaye toh turant error log check karo aur mujhe batao!
