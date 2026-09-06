# ⚡ TCM 2.0 - Quick Deployment Guide

## 🚀 Upload These Files (Upload करें ये Files)

### Modified Files (4):
```
1. src/Controllers/Student/DashboardController.php
2. views/layouts/student.php
3. views/student/courses/show.php
4. views/student/dashboard.php
```

### New Files (Upload all files in these folders):
```
src/Models/Wallet.php
src/Models/DailyTask.php
src/Models/CourseNote.php
src/Services/OpenRouterService.php
src/Commands/GenerateDailyTasks.php

src/Controllers/Student/WalletController.php
src/Controllers/Student/AgentController.php
src/Controllers/Student/TaskController.php
src/Controllers/Student/NoteController.php

views/student/wallet/
views/student/agent/
views/student/tasks/
views/student/notes/

cron-daily-tasks.php
migrate_referral_payments.php
```

---

## 🗄️ Run This SQL (एक बार में सभी SQL Run करें)

**Copy-paste this entire block in phpMyAdmin:**

```sql
-- 1. Referral columns
ALTER TABLE payment_submissions 
ADD COLUMN referral_code VARCHAR(50) DEFAULT NULL AFTER amount,
ADD COLUMN referrer_id INT DEFAULT NULL AFTER referral_code,
ADD INDEX idx_referral (referral_code),
ADD INDEX idx_referrer (referrer_id);

-- 2. Wallets
CREATE TABLE IF NOT EXISTS wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    balance DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Wallet Transactions
CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Withdrawal Requests
CREATE TABLE IF NOT EXISTS withdrawal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    upi_id VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Daily Tasks
CREATE TABLE IF NOT EXISTS daily_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    estimated_time INT DEFAULT 15,
    assigned_date DATE NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'skipped') DEFAULT 'pending',
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_id, assigned_date),
    INDEX idx_status (status),
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Enrollment Duration
ALTER TABLE enrollments 
ADD COLUMN duration_days INT DEFAULT NULL AFTER progress,
ADD COLUMN enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER duration_days,
ADD COLUMN expires_at TIMESTAMP NULL AFTER enrolled_at;

-- 7. Course Notes
CREATE TABLE IF NOT EXISTS course_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    parent_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    content LONGTEXT,
    order_num INT DEFAULT 0,
    is_chapter BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_course (course_id),
    INDEX idx_parent (parent_id),
    INDEX idx_slug (slug),
    INDEX idx_order (order_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Student Note Progress
CREATE TABLE IF NOT EXISTS student_note_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    note_id INT NOT NULL,
    last_read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_note (user_id, note_id),
    INDEX idx_user (user_id),
    INDEX idx_note (note_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## ⚙️ Add to .env File

```env
# Add this line at the end of .env
OPENROUTER_API_KEY=your-openrouter-api-key-here
```

Get your free API key from: https://openrouter.ai/

---

## ⏰ Setup Cron Jobs (Optional - For Auto Tasks)

**In cPanel → Cron Jobs, add these two:**

### 1. Daily Reminder (12:00 PM every day):
```
0 12 * * * /usr/bin/php /home/youruser/public_html/cron-daily-tasks.php reminder
```

### 2. Weekly Generation (12:00 PM every Friday):
```
0 12 * * 5 /usr/bin/php /home/youruser/public_html/cron-daily-tasks.php generate
```

---

## ✅ Quick Test Checklist

After uploading, test these:

### 1. Dashboard Test:
- [ ] Login as student
- [ ] See 3 gradient widgets (purple, pink, blue)
- [ ] Click wallet widget → goes to /student/wallet
- [ ] Click tasks widget → goes to /student/tasks
- [ ] Click agent widget → goes to /student/agent

### 2. Navigation Test:
- [ ] Sidebar shows "Daily Tasks" menu item
- [ ] Header shows wallet balance badge (₹ amount in purple)
- [ ] Clicking header badge goes to wallet

### 3. Course Test:
- [ ] Go to an enrolled course page
- [ ] See "Read Course Notes" button
- [ ] Click it → goes to notes page

### 4. Feature Test:
- [ ] Submit payment with referral code
- [ ] Admin approve payment
- [ ] Referrer gets ₹100 in wallet
- [ ] Generate daily tasks
- [ ] Chat with agent
- [ ] Read course notes

---

## 🎯 What Students Will See

### Dashboard:
```
┌────────────────────────────────────────┐
│ Good morning, John! 👋                 │
│ [Browse Courses] [Programs] [Payments] │
├────────────────────────────────────────┤
│ [Student ID: STD001] [Referral: REF123]│
├────────────────────────────────────────┤
│ Stats: 3 Enrolled | 2 Events | 5 Certs│
├────────────────────────────────────────┤
│ ┌─────────┐ ┌─────────┐ ┌──────────┐ │
│ │💰 Wallet│ │📋 Tasks │ │🤖 Agent  │ │
│ │ ₹500.00 │ │    3    │ │  Ask Me  │ │
│ │ View →  │ │2 done ✓ │ │ Anything │ │
│ └─────────┘ └─────────┘ └──────────┘ │
├────────────────────────────────────────┤
│ 📋 Today's Tasks          View all →  │
│ ┌────────────────────────────────────┐│
│ │○ Complete React tutorial          ││
│ │  ⏱ 30 min · 📚 Full Stack Dev     ││
│ └────────────────────────────────────┘│
├────────────────────────────────────────┤
│ My Courses | Payment History          │
│ Live Sessions | Portfolio              │
└────────────────────────────────────────┘
```

### Navigation Sidebar:
```
☰ Dashboard
📚 My Courses
📚 Programs
📅 Events
✅ Daily Tasks      ← NEW!
👥 Community
💬 Chat & Help
💰 Wallet
🤖 TCM Agent        ← NEW!
🧾 Payments
📄 Applications
💼 Portfolio
⚙️ Profile
```

### Header (Top Right):
```
[🔔] [💰 ₹500] [John 👤] [⎋]
      ↑ NEW wallet badge!
```

---

## � Key Features

### 1. Wallet System 💰
- Students earn ₹100 per referral
- Withdraw when balance ≥ ₹300
- Track all transactions
- UPI withdrawal

### 2. Daily Tasks 📋
- AI generates 3-5 tasks weekly
- Based on enrolled courses
- Track completion
- Daily reminders

### 3. TCM Agent 🤖
- AI chat assistant
- Instant help
- WhatsApp-style UI
- Quick suggestions

### 4. Course Notes 📚
- W3Schools-style reading
- Chapter-wise navigation
- Progress tracking
- Access during enrollment

---

## 🆘 Common Problems & Fixes

### Problem: Widgets not showing
**Fix:** Clear browser cache, check file uploaded correctly

### Problem: Database error
**Fix:** Run SQL again, check all tables created

### Problem: Tasks page empty
**Fix:** Add OpenRouter API key, click "Generate Tasks"

### Problem: Wallet shows ₹0
**Fix:** Make test payment with referral, admin approve it

---

## 📞 Support

Check error logs:
```bash
tail -f /var/log/php_errors.log
tail -f /var/log/apache2/error.log
```

Or in cPanel → Error Logs

---

## � Success!

If you see:
✅ Dashboard with 3 gradient widgets
✅ Navigation menu with "Daily Tasks"
✅ Header with wallet badge
✅ Course pages with "Read Notes" button

**Congratulations! Deployment successful!** 🚀

---

## 📚 Full Documentation

For detailed information, read:
- `FINAL_DEPLOYMENT_READY.md` - Complete deployment guide
- `DASHBOARD_INTEGRATION_COMPLETE.md` - Dashboard details
- `DASHBOARD_INTEGRATION_HINDI.md` - Hindi guide
- `UI_FIXES_APPLIED.md` - UI changes

---

**Ready to go live? Upload files → Run SQL → Test → Celebrate!** 🎊

