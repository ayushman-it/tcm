# 🤖 AI Task Generator System - पूर्ण Setup

## ✨ क्या बनाया गया?

Aapke students के लिए **AI-powered automatic task generation system**:

### Features:
- ✅ OpenRouter AI integration (trained model)
- ✅ Student ke courses aur progress analyze karta hai
- ✅ Har Friday naye tasks generate hote hain
- ✅ Daily 12 noon pe notification bhejta hai
- ✅ Beautiful task dashboard
- ✅ Task completion tracking
- ✅ Push notifications ready

---

## 📂 बनाई गई Files

### 1. Core System (5 files):
```
src/Models/DailyTask.php                 - Database & task logic
src/Services/OpenRouterService.php       - AI API calls
src/Commands/GenerateDailyTasks.php      - Cron command
src/Controllers/Student/TaskController.php - UI controller
views/student/tasks/index.php            - Task list page
```

### 2. Configuration:
```
config/config.php          - OpenRouter config added
.env.example               - API key template
cron-daily-tasks.php       - Cron entry point
```

### 3. Documentation:
```
AI_TASK_SYSTEM_SETUP.md    - English setup guide
AI_TASK_SYSTEM_HINDI.md    - यह file (Hindi guide)
```

---

## 🗄️ Database Setup

### MySQL me ye table banao:

```sql
CREATE TABLE daily_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    course_id BIGINT UNSIGNED DEFAULT NULL,
    course_title VARCHAR(200) DEFAULT NULL,
    difficulty ENUM('Easy','Medium','Hard') DEFAULT 'Medium',
    estimated_time INT DEFAULT 30,
    tags JSON DEFAULT NULL,
    motivation VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','in_progress','completed','skipped') DEFAULT 'pending',
    completed_at DATETIME DEFAULT NULL,
    week_start DATE NOT NULL,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user_date (user_id, week_start, status),
    CONSTRAINT fk_dt_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🔑 OpenRouter API Setup

### Step 1: API Key लो

1. Visit: **https://openrouter.ai/keys**
2. Sign up करो (email se)
3. New API key बनाओ
4. Key copy करो: `your-openrouter-api-key-here`

### Step 2: .env file me add karo

```env
# OpenRouter AI
OPENROUTER_API_KEY=your-openrouter-api-key-here
```

**Important:** Real key dalना, example key नहीं!

---

## ⏰ Cron Job Setup (Schedule)

### Server pe ye cron jobs setup karo:

```bash
# crontab edit karo
crontab -e

# Ye 2 lines add karo:

# 1. Daily 12 noon - Notifications bhejo
0 12 * * * cd /path/to/tcm-2.0 && php cron-daily-tasks.php

# 2. Friday 12 noon - Naye tasks generate karo
0 12 * * 5 cd /path/to/tcm-2.0 && php cron-daily-tasks.php
```

**Replace:** `/path/to/tcm-2.0` apne actual path se!

### cPanel me Cron Setup:

```
Command: cd /home/username/public_html/tcm-2.0 && php cron-daily-tasks.php
Minute: 0
Hour: 12
Day: * (har din)
Month: * (har month)
Weekday: * (har weekday)
```

---

## 🚀 Routes Added

```php
// In app.php (already added)

GET  /student/tasks               - Task list dekhne ke liye
POST /student/tasks/{id}/status   - Task complete mark karne ke liye
GET  /student/tasks/generate      - Manual tasks generate karne ke liye
```

---

## 🧪 Testing

### Test 1: Manual Task Generation

```bash
# Local testing
cd c:\xampp\htdocs\tcm\tcm-2.0
c:\xampp\php\php.exe cron-daily-tasks.php
```

**Expected output:**
```
🤖 Starting Daily Task Generation...
📅 Today is Friday - Generating NEW weekly tasks
  Generating for Rahul Kumar... ✓ (4 tasks)
  Generating for Priya Singh... ✓ (3 tasks)
✅ Task generation complete!
```

### Test 2: Browser Testing

1. Login as student
2. Go to: `http://localhost/tcm/tcm-2.0/student/tasks`
3. Tasks should be visible!
4. Click checkbox to complete task

### Test 3: Database Check

```sql
-- Tasks created?
SELECT * FROM daily_tasks ORDER BY id DESC LIMIT 10;

-- Student's tasks
SELECT * FROM daily_tasks WHERE user_id = 1;
```

---

## 🎯 How It Works

### Friday Flow (New Tasks):

```
Friday 12:00 PM
     ↓
Cron Job Trigger
     ↓
System:
  1. Get all active students
  2. For each student:
     ├─ Get enrolled courses
     ├─ Calculate progress (completed lessons)
     ├─ Send data to OpenRouter AI
     ├─ AI generates 3-5 personalized tasks
     ├─ Save tasks in database
     └─ Send notification
     ↓
Student Receives:
  ✅ Push notification
  ✅ In-app notification
  ✅ Can view on /student/tasks page
```

### Daily Flow (Reminders):

```
Daily 12:00 PM (Mon-Sun)
     ↓
Cron Job Trigger
     ↓
System:
  1. Find students with pending tasks
  2. For each student:
     ├─ Count pending tasks
     ├─ Send reminder notification
     └─ "You have X tasks waiting!"
     ↓
Student receives reminder
```

---

## 🤖 AI Prompt Example

OpenRouter ko yeh information bhejte hain:

```
Student: Rahul Kumar
Progress: 45% (23/50 lessons completed)

Enrolled Courses:
- Full Stack Development (60% complete) - Current: React Hooks
- Python for Beginners (30% complete) - Current: Functions

AI generates:
1. 📚 Complete React Hooks lesson in Full Stack course (Medium, 45 min)
2. 💻 Build a simple counter app using useState (Easy, 30 min)
3. 🐍 Practice Python functions with 5 exercises (Medium, 40 min)
4. 🎯 Review JavaScript ES6 arrow functions (Easy, 20 min)
```

---

## 💰 Cost (बहुत सस्ता!)

### OpenRouter Pricing:

**GPT-4O-Mini** (जो हम use कर रहे हैं):
- Input: $0.15 per 1M tokens
- Output: $0.60 per 1M tokens

### Real Cost:
- 1 student = ~500 tokens = $0.0005
- 100 students/week = $0.05
- **Monthly cost: ₹2-3 rupees only!** 🎉

पूरी तरह affordable!

---

## 📱 Notifications

### Automatic Notifications Bhejte Hain:

1. **Friday (New Tasks)**:
   ```
   Title: "🎯 New Learning Tasks Generated!"
   Body: "Your personalized tasks for this week are ready. 4 activities to boost your skills!"
   ```

2. **Daily (Reminder)**:
   ```
   Title: "⏰ Daily Reminder"
   Body: "You have 3 tasks waiting for you today. Let's make progress! 💪"
   ```

### Notification Types:
- ✅ Push notifications (if Firebase configured)
- ✅ In-app notifications (bell icon)

---

## 🎨 UI Features

### Task Dashboard:
- 📊 Progress stats (Total, Completed, Pending)
- ✅ Checkbox to mark complete
- 📚 Course association shown
- ⏱️ Estimated time display
- 🎯 Difficulty badges (Easy/Medium/Hard)
- 💪 Motivational messages
- 🔄 Regenerate button (manual)

### Task Card Example:
```
┌─────────────────────────────────────────┐
│ [✓] 📚 Complete React Hooks lesson     │
│     Medium | ~45 min                    │
│                                          │
│     Build a component using useState     │
│     and useEffect hooks. Practice the    │
│     concepts from today's lesson.        │
│                                          │
│     📖 Full Stack Development            │
│     💪 You're making great progress!     │
└─────────────────────────────────────────┘
```

---

## 🔧 Customization

### Task Count Change:

File: `src/Services/OpenRouterService.php`

```php
// Line 50:
// Generate 3-5 specific, actionable tasks
// Change to: Generate 5-7 tasks
```

### AI Model Change:

```php
// Default (सस्ता, अच्छा)
private string $model = 'openai/gpt-4o-mini';

// Better quality (थोड़ा महंगा)
private string $model = 'openai/gpt-4-turbo';

// Claude (alternative)
private string $model = 'anthropic/claude-3-sonnet';
```

### Time Estimates:

```php
// Default 30 minutes
'estimated_time' => 30,

// Change to 60 minutes
'estimated_time' => 60,
```

---

## 🐛 Common Problems & Solutions

### Problem 1: Tasks generate nahi ho rahe

**Solution:**
```bash
# Check API key
echo $OPENROUTER_API_KEY

# Manual test
php cron-daily-tasks.php

# Check error log
tail -f /var/log/php_errors.log
```

### Problem 2: Cron job run nahi ho raha

**Solution:**
```bash
# Check cron is active
service cron status

# Check cron logs
tail -f /var/log/cron.log

# Test path
cd /path/to/tcm-2.0 && pwd
```

### Problem 3: Notifications nahi aa rahe

**Solution:**
```sql
-- Check notifications table
SELECT * FROM notifications ORDER BY id DESC LIMIT 5;

-- Check Firebase tokens
SELECT COUNT(*) FROM users WHERE device_token IS NOT NULL;
```

---

## 📊 Analytics

### Check System Usage:

```sql
-- Total tasks
SELECT COUNT(*) as total_tasks FROM daily_tasks;

-- Completion rate
SELECT 
    ROUND(COUNT(CASE WHEN status='completed' THEN 1 END) / COUNT(*) * 100, 2) as completion_rate
FROM daily_tasks;

-- Most active students
SELECT u.name, COUNT(*) as tasks_completed
FROM daily_tasks dt
JOIN users u ON u.id = dt.user_id
WHERE dt.status = 'completed'
GROUP BY u.id
ORDER BY tasks_completed DESC
LIMIT 10;

-- This week's tasks
SELECT COUNT(*) FROM daily_tasks 
WHERE week_start = (
    SELECT DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) - 4 DAY)
);
```

---

## ✅ Server Upload Checklist

### Files to Upload:

```
✅ src/Models/DailyTask.php
✅ src/Services/OpenRouterService.php
✅ src/Commands/GenerateDailyTasks.php
✅ src/Controllers/Student/TaskController.php
✅ views/student/tasks/index.php
✅ config/config.php (modified)
✅ app.php (modified)
✅ cron-daily-tasks.php
```

### Setup Steps on Server:

1. ✅ Upload files
2. ✅ Create `daily_tasks` table
3. ✅ Add OpenRouter API key to `.env`
4. ✅ Setup cron jobs
5. ✅ Test: `php cron-daily-tasks.php`
6. ✅ Check `/student/tasks` page

---

## 🎉 Success Indicators

System working properly hai agar:

- ✅ Cron job Friday ko run ho raha hai
- ✅ Tasks database me save ho rahe hain
- ✅ Students ko notifications mil rahe hain
- ✅ `/student/tasks` page load ho raha hai
- ✅ Task completion working hai
- ✅ Daily reminders aa rahe hain

---

## 📞 Support

### Error देखने के lिए:

```bash
# PHP errors
tail -f /var/log/php_errors.log

# Cron logs
tail -f /var/log/cron.log

# Apache errors
tail -f /var/log/apache2/error.log
```

### Debug Mode:

```php
// In cron-daily-tasks.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 🚀 Final Steps

```bash
# 1. API Key add karo
nano .env
# Add: OPENROUTER_API_KEY=your-openrouter-api-key-here

# 2. Database table banao
mysql -u root -p tcm < daily_tasks.sql

# 3. Test karo
php cron-daily-tasks.php

# 4. Cron setup karo
crontab -e
# Add: 0 12 * * * cd /path && php cron-daily-tasks.php

# 5. Browser test
# Open: https://yourdomain.com/student/tasks
```

**System Live Hai! 🎉**

---

Koi problem ho to `php cron-daily-tasks.php` run karke errors dekho!
