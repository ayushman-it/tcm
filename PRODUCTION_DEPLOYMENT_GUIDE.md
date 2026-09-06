# 🚀 Production Deployment Guide - thecodemunk.in

## 🎯 Overview
Steps to deploy the new auto-concepts feature on production server.

---

## 📋 Pre-Deployment Checklist

### ✅ Files to Upload:

```
src/Controllers/Student/
  ├─ CourseController.php (MODIFIED)
  └─ LessonConceptsController.php (NEW)

views/student/
  └─ dashboard.php (MODIFIED)

app.php (MODIFIED - new routes)

database/
  └─ lesson_content.sql (if not already exists)

.env (UPDATE - add GEMINI_API_KEY)
```

---

## 🔧 Step 1: Backup Production

### **On Server (SSH/cPanel):**

```bash
# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf backup_files_$(date +%Y%m%d).tar.gz \
    src/Controllers/Student/ \
    views/student/dashboard.php \
    app.php
```

---

## 📤 Step 2: Upload Files

### **Option A: Via FTP/SFTP**
Upload these files to production:
```
/src/Controllers/Student/CourseController.php
/src/Controllers/Student/LessonConceptsController.php
/views/student/dashboard.php
/app.php
```

### **Option B: Via Git**
```bash
git add .
git commit -m "Add auto AI concepts feature"
git push origin main

# On server
git pull origin main
```

---

## 🗄️ Step 3: Update Database

### **Check if table exists:**
```bash
mysql -u username -p database_name -e "SHOW TABLES LIKE 'lesson_content'"
```

### **If NOT exists, create it:**
```bash
mysql -u username -p database_name < database/lesson_content.sql
```

### **Verify:**
```bash
mysql -u username -p database_name -e "DESCRIBE lesson_content"
```

Should show columns: `id`, `lesson_id`, `overview_hi`, `overview_en`, `key_concepts`, etc.

---

## 🔑 Step 4: Update Environment Variables

### **Edit `.env` file:**
```bash
nano .env
```

### **Add Gemini API Key:**
```env
# Google Gemini API (FREE)
GEMINI_API_KEY=AIzaSyBDcFdVwbhai...

# Or OpenAI (if you prefer)
OPENAI_API_KEY=sk-proj-...
```

**Get Gemini API Key:**
```
https://makersuite.google.com/app/apikey
```

---

## ✅ Step 5: Test on Production

### **Test 1: Run Debug Script**
```
https://thecodemunk.in/prod-debug.php
```

**Login as admin first, then visit this URL.**

This will check:
- ✅ Database connection
- ✅ Tables exist
- ✅ Controllers loaded
- ✅ Files present
- ✅ API keys set

### **Test 2: Access Dashboard**
```
https://thecodemunk.in/student
```

Should load without errors.

### **Test 3: Expand Course**
1. Login as student
2. Go to dashboard
3. Click on any enrolled course
4. Should expand and show lessons

### **Test 4: Click Lesson**
1. Click on any lesson
2. Should show "Generating AI concepts..."
3. Wait 30-60 seconds
4. Concepts should appear

---

## 🐛 Troubleshooting on Production

### **Issue 1: 500 Error**

**Check error logs:**
```bash
# Apache error log
tail -f /var/log/apache2/error.log

# PHP error log
tail -f /var/log/php/error.log

# Application log
tail -f storage/logs/error.log
```

**Common causes:**
- Missing `lesson_content` table
- Wrong file permissions
- Syntax error in uploaded files
- Autoloader cache issue

**Solution:**
```bash
# Clear cache
php artisan cache:clear  # if using Laravel
# OR
rm -rf var/cache/*       # if using Symfony
# OR
opcache_reset()          # PHP opcache
```

---

### **Issue 2: Class Not Found**

**Check autoloader:**
```bash
# Regenerate autoloader
composer dump-autoload

# Verify file exists
ls -la src/Controllers/Student/LessonConceptsController.php
```

**Check permissions:**
```bash
chmod 644 src/Controllers/Student/*.php
chown www-data:www-data src/Controllers/Student/*.php
```

---

### **Issue 3: Database Connection Failed**

**Verify credentials:**
```bash
mysql -u username -p database_name -e "SELECT 1"
```

**Check `.env` file:**
```bash
cat .env | grep DB_
```

Ensure:
- `DB_HOST` is correct
- `DB_DATABASE` is correct
- `DB_USERNAME` has permissions
- `DB_PASSWORD` is correct

---

### **Issue 4: API Key Issues**

**Check if set:**
```bash
cat .env | grep GEMINI_API_KEY
```

**Test API key:**
```bash
curl -X POST \
  https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=YOUR_KEY \
  -H 'Content-Type: application/json' \
  -d '{"contents":[{"parts":[{"text":"Hello"}]}]}'
```

Should return JSON response, not error.

---

### **Issue 5: File Permissions**

**Check and fix:**
```bash
# Files should be 644
find . -type f -name "*.php" -exec chmod 644 {} \;

# Directories should be 755
find . -type d -exec chmod 755 {} \;

# Storage should be writable
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

---

## 🔒 Security Checklist

### **1. Hide Debug Script**
After deployment, delete or protect:
```bash
rm prod-debug.php
# OR
# Add password protection in .htaccess
```

### **2. Disable Debug Mode**
In `config/config.php`:
```php
// Production settings
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED);
```

### **3. Secure API Keys**
```bash
# .env should NOT be web-accessible
chmod 600 .env
```

Add to `.htaccess`:
```apache
<Files .env>
    Require all denied
</Files>
```

### **4. Enable HTTPS**
Ensure all API calls use HTTPS:
```php
// In AIContentGenerator.php
$this->apiEndpoint = 'https://...'; // Not http://
```

---

## 📊 Monitoring

### **1. Check Logs Daily**
```bash
tail -f storage/logs/error.log
```

### **2. Monitor API Usage**
```sql
SELECT COUNT(*) as total_generations,
       COUNT(DISTINCT lesson_id) as unique_lessons,
       ai_model
FROM lesson_content
WHERE ai_generated = 1
GROUP BY ai_model;
```

### **3. Track Performance**
```sql
SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, generated_at)) as avg_time
FROM lesson_content
WHERE ai_generated = 1 AND generated_at IS NOT NULL;
```

---

## 🚀 Post-Deployment Tasks

### **1. Test with Real Users**
- Ask 2-3 students to test
- Monitor for errors
- Gather feedback

### **2. Monitor First 24 Hours**
- Check error logs every 2 hours
- Monitor API usage
- Watch for performance issues

### **3. Optimize if Needed**
```php
// Add caching
$cache->remember("lesson_concepts_{$lessonId}", 3600, function() {
    return $this->aiGenerator->getContent($lessonId);
});
```

---

## 🎉 Success Criteria

✅ Dashboard loads without errors
✅ Courses expand properly
✅ Lessons display correctly
✅ Concepts generate within 60 seconds
✅ Cached content loads instantly
✅ No 500 errors in logs
✅ Students can access feature
✅ API calls work properly

---

## 📞 Support Commands

### **Quick Health Check:**
```bash
# Check if services are running
systemctl status apache2
systemctl status mysql

# Check disk space
df -h

# Check memory
free -m

# Check MySQL connections
mysql -u root -p -e "SHOW PROCESSLIST"
```

### **Emergency Rollback:**
```bash
# Restore database
mysql -u username -p database_name < backup_20260620.sql

# Restore files
tar -xzf backup_files_20260620.tar.gz
```

---

## 📝 Deployment Log Template

```markdown
## Deployment - Auto Concepts Feature

**Date:** 2026-06-20
**Time:** [HH:MM]
**Environment:** Production (thecodemunk.in)

### Files Uploaded:
- [x] CourseController.php
- [x] LessonConceptsController.php
- [x] dashboard.php
- [x] app.php

### Database:
- [x] lesson_content table created
- [x] Verified structure

### Configuration:
- [x] GEMINI_API_KEY added
- [x] Permissions set correctly

### Testing:
- [x] prod-debug.php passed all checks
- [x] Dashboard loads
- [x] Courses expand
- [x] Concepts generate
- [x] No errors in logs

### Issues:
- None / [List any issues]

### Rollback Plan:
- Database backup: backup_20260620.sql
- Files backup: backup_files_20260620.tar.gz

**Status:** ✅ SUCCESS / ❌ FAILED
**Notes:** [Any additional notes]
```

---

## ✅ Final Checklist

Before going live:

- [ ] Backup completed
- [ ] Files uploaded
- [ ] Database updated
- [ ] `.env` configured
- [ ] Permissions set
- [ ] prod-debug.php passed
- [ ] Manual testing done
- [ ] Error logs checked
- [ ] Debug mode disabled
- [ ] API keys secured
- [ ] Monitoring enabled

---

**Ready to deploy! 🚀**

Follow steps sequentially, test thoroughly, and monitor closely for first 24 hours.
