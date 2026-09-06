# 🤖 AI Task Generator System - Complete Setup Guide

## 📋 Overview

Ye system students ke liye automatically daily tasks generate karta hai using OpenRouter AI:

✅ **Features:**
- AI-powered personalized task generation based on enrolled courses
- Student progress tracking aur analysis
- Daily 12 noon pe notifications
- Friday ko har hafte naye tasks generate hote hain
- Push notifications (Firebase)
- Beautiful UI with task completion tracking

---

## 🗄️ Database Setup

### Step 1: Create Tables

MySQL/phpMyAdmin me ye SQL run karo:

```sql
-- Daily Tasks table
CREATE TABLE IF NOT EXISTS daily_tasks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    course_id BIGINT UNSIGNED DEFAULT NULL,
    course_title VARCHAR(200) DEFAULT NULL,
    difficulty ENUM('Easy','Medium','Hard') DEFAULT 'Medium',
    estimated_time INT DEFAULT 30 COMMENT 'minutes',
    tags JSON DEFAULT NULL,
    motivation VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','in_progress','completed','skipped') DEFAULT 'pending',
    completed_at DATETIME DEFAULT NULL,
    week_start DATE NOT NULL COMMENT 'Friday of the week',
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notified_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_date (user_id, week_start, status),
    KEY idx_course (course_id),
    CONSTRAINT fk_dt_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔑 OpenRouter API Key Setup

### Step 1: Get API Key

1. Visit: https://openrouter.ai/keys
2. Sign up/Login
3. Create new API key
4. Copy the key

### Step 2: Add to .env

`.env` file me ye line add karo:

```env
# OpenRouter AI (for task generation)
OPENROUTER_API_KEY=your-openrouter-api-key-here
```

### Step 3: Test Connection

```bash
php cron-daily-tasks.php
```

Success message aana chahiye!

---

## ⏰ Cron Job Setup (Server pe)

### Automatic Task Generation Schedule:

Server pe crontab edit karo:

```bash
crontab -e
```

Ye lines add karo:

```cron
# Daily at 12:00 PM - Send task notifications to students
0 12 * * * cd /path/to/tcm-2.0 && php cron-daily-tasks.php

# Every Friday at 12:00 PM - Generate new weekly tasks
0 12 * * 5 cd /path/to/tcm-2.0 && php cron-daily-tasks.php
```

**Important:** `/path/to/tcm-2.0` ko apne actual path se replace karo!

### cPanel Cron Setup:

```
Minute: 0
Hour: 12
Day: *
Month: *
Weekday: *
Command: cd /home/username/public_html/tcm-2.0 && php cron-daily-tasks.php
```

---

## 🧪 Manual Testing

### Test 1: Generate Tasks Manually

```bash
# Test for a specific student
php cron-daily-tasks.php
```

### Test 2: Check Database

```sql
-- Check if tasks were created
SELECT * FROM daily_tasks ORDER BY id DESC LIMIT 10;
```

### Test 3: Test via Browser

```
1. Login as student
2. Go to: /student/tasks
3. Click "Generate My Tasks"
4. Tasks should appear!
```

---

## 📊 How It Works

### Flow Diagram:

```
Friday 12:00 PM
     ↓
Cron Job Runs
     ↓
For Each Active Student:
  ├─ Get enrolled courses
  ├─ Calculate progress
  ├─ Call OpenRouter AI API
  ├─ AI generates 3-5 personalized tasks
  ├─ Save to database
  └─ Send notification
     ↓
Student receives:
  ├─ Push notification (Firebase)
  ├─ In-app notification
  └─ Can view on /student/tasks
```

### Daily Notifications (Mon-Sun at 12:00 PM):

```
Cron Job Runs
     ↓
For Each Student with Pending Tasks:
  ├─ Count pending tasks
  ├─ Send reminder notification
  └─ "You have X tasks waiting!"
```

---

## 🎨 UI Components

### Task Card Features:
- ✅ Checkbox to mark complete
- 📚 Course association
- ⏱️ Estimated time
- 🎯 Difficulty level (Easy/Medium/Hard)
- 💪 Motivational message
- 📊 Progress stats

### Dashboard Integration:
Tasks widget on student dashboard showing today's tasks

---

## 🤖 AI Prompt Customization

Edit: `src/Services/OpenRouterService.php`

```php
private function buildTaskPrompt(...) {
    return <<<PROMPT
// Customize this prompt to change task style
You are a personalized learning assistant...
PROMPT;
}
```

### AI Models Available:

```php
// Cost-effective (Default)
private string $model = 'openai/gpt-4o-mini';

// More powerful
private string $model = 'openai/gpt-4-turbo';

// Anthropic Claude
private string $model = 'anthropic/claude-3-sonnet';
```

---

## 📱 Notification Setup

Notifications automatically sent via:
1. **In-app** - Notification bell
2. **Push** - Firebase Cloud Messaging (if configured)

No extra setup needed! Already integrated.

---

## 🔧 Configuration Options

### Change Task Count:

Edit `src/Services/OpenRouterService.php`:

```php
// Generate 3-5 specific, actionable tasks
// Change to: Generate 5-7 tasks
```

### Change Difficulty Mix:

```php
// In AI prompt, add:
// "2 Easy tasks, 2 Medium tasks, 1 Hard task"
```

### Change Time Estimates:

```php
'estimated_time' => 30, // Change default from 30 to 60 minutes
```

---

## 📂 Files Created

### Core Files:
```
src/Models/DailyTask.php              - Task model & database operations
src/Services/OpenRouterService.php    - AI API integration
src/Commands/GenerateDailyTasks.php   - Cron command
src/Controllers/Student/TaskController.php - Task UI controller
views/student/tasks/index.php         - Task list UI
cron-daily-tasks.php                  - Cron entry point
```

### Routes Added:
```php
GET  /student/tasks              - View tasks
POST /student/tasks/{id}/status  - Update task status
GET  /student/tasks/generate     - Manual generation
```

---

## 🚀 Deployment Checklist

### Before Going Live:

- [ ] OpenRouter API key added to `.env`
- [ ] Database table created
- [ ] Cron job configured on server
- [ ] Test manual generation
- [ ] Test notification delivery
- [ ] Check Firebase credentials (for push)

### Test Commands:

```bash
# Test task generation
php cron-daily-tasks.php

# Check cron is working
tail -f /var/log/cron.log  # Linux
# Or check cPanel cron job logs
```

---

## 💰 Cost Estimation

### OpenRouter Pricing:

**GPT-4O-Mini** (Default):
- Input: $0.15 per 1M tokens
- Output: $0.60 per 1M tokens

**Average cost per student per week:**
- ~500 tokens per generation = $0.0005 (negligible!)
- 100 students × 52 weeks = $2.60/year

**Very affordable!** 🎉

---

## 🐛 Troubleshooting

### Problem: Tasks not generating

**Solution:**
```bash
# Check API key
echo $OPENROUTER_API_KEY

# Check cron logs
tail -f /var/log/cron.log

# Run manually to see errors
php cron-daily-tasks.php
```

### Problem: API timeout

**Solution:**
```php
// Increase timeout in OpenRouterService.php
CURLOPT_TIMEOUT => 60, // From 30 to 60
```

### Problem: No notifications

**Solution:**
```sql
-- Check if notifications table exists
SHOW TABLES LIKE 'notifications';

-- Check if Firebase configured
SELECT * FROM users WHERE device_token IS NOT NULL LIMIT 1;
```

---

## 📈 Analytics & Monitoring

### Track Usage:

```sql
-- Total tasks generated
SELECT COUNT(*) FROM daily_tasks;

-- Completion rate
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
    ROUND(SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as rate
FROM daily_tasks;

-- Most active students
SELECT user_id, COUNT(*) as tasks_completed
FROM daily_tasks 
WHERE status='completed'
GROUP BY user_id
ORDER BY tasks_completed DESC
LIMIT 10;
```

---

## 🎯 Success Metrics

After deployment, track:
- ✅ Task completion rate
- ✅ Student engagement (logins)
- ✅ Course progress improvement
- ✅ Notification open rate

---

## 🔄 Future Enhancements

Possible additions:
- [ ] Task difficulty adjustment based on performance
- [ ] Streak tracking & badges
- [ ] Weekly summary emails
- [ ] Team challenges
- [ ] AI-powered feedback on completed tasks

---

## ✅ Quick Start Summary

```bash
# 1. Add API key to .env
echo "OPENROUTER_API_KEY=your-openrouter-api-key-here" >> .env

# 2. Create database table
mysql -u root -p tcm < database/daily_tasks.sql

# 3. Test generation
php cron-daily-tasks.php

# 4. Setup cron (Friday + Daily noon)
0 12 * * * cd /path && php cron-daily-tasks.php

# 5. Open browser
https://yourdomain.com/student/tasks
```

**Done! System is live!** 🚀

---

Need help? Check logs:
- PHP errors: `/var/log/php_errors.log`
- Cron logs: `/var/log/cron.log`
- App logs: `error_log()` outputs

**System is production-ready!** 🎉
