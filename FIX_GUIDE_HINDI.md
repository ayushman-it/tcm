# Fix Guide - TCM 2.0 (हिंदी)

## 🎯 क्या-क्या Fix हुआ है

### 1. ✅ Payment Error Fix (500 - Something went wrong)

**समस्या**: Payment submit करते समय "500 - Something went wrong" error आ रहा था।

**कारण**: Database table `payment_submissions` में `referral_code` और `referrer_id` columns missing थे जो code में use हो रहे थे।

**समाधान**:
- नया migration script बनाया: `migrate_referral_columns.php`
- SQL file update किया: `database/add_referral_to_payments.sql`
- अब columns automatically add हो जायेंगे

**Migration कैसे चलाएं**:
```bash
cd c:\xampp\htdocs\tcm\tcm-2.0
php migrate_referral_columns.php
```

**Output ऐसा दिखेगा**:
```
🔧 Starting Referral Columns Migration...

📋 Adding referral columns to payment_submissions table...
   ✓ Added referral_code column
   ✓ Added referrer_id column
   ✓ Added index on referrer_id
   ✓ Added foreign key constraint

✅ Migration completed successfully!
🎉 Payment submission form can now accept referral codes!
```

---

### 2. ✅ Daily Tasks - Progress के हिसाब से Problems देना

**समस्या**: Tasks generic हो रहे थे ("next lesson complete करो" type), specific practice problems नहीं मिल रहे थे based on completed lessons.

**पुराना System**:
- Generic tasks: "Complete next lesson"
- Progress tracking limited
- Koi specific problems nahi

**नया System**:
- Tasks अब **completed lessons** के हिसाब से बनते हैं
- Track करता है कि student ने exactly कौन से lessons complete किए
- Next incomplete lessons का data use करके specific practice problems देता है
- 3 तरह के tasks:
  1. **Active Learning**: अगर course में lessons बाकी हैं तो उनके लिए practice
  2. **Completed Course**: Course complete होने पर project challenges  
  3. **Recent Practice**: हाल में complete किए lessons के concepts पर hands-on

**Example Task (पहले vs अब)**:

*पहले*:
```
📚 Continue Learning: JavaScript Basics
Complete the next lesson in JavaScript Basics and practice the concepts.
```

*अब*:
```
🔥 Challenge: Todo App बनाओ Array Methods से
JavaScript array methods (map, filter, reduce) use करके एक simple todo 
application बनाओ जो आपने "Array Operations" lesson में सीखा। 
Add, remove और filter features include करो।
Difficulty: Medium | Time: 60 mins
```

**Technical Details**:

**`src/Models/DailyTask.php` में Changes**:
- `getStudentProgress()`: अब actual completed lessons track करता है `lesson_progress` table से
- `getNextLessons()`: अगले 3 incomplete lessons find करता है
- `createFallbackTasks()`: Smart progress-based problems generate करता है

**`src/Services/OpenRouterService.php` में Changes**:
- AI prompt update किया - अब **practice problems** focus में है
- Detailed progress context देता है:
  - Per course kitne lessons complete
  - Next कौन से lessons करने हैं
  - हाल में कौन से lessons complete किए
- AI specific coding challenges generate करता है learned concepts पर

---

### 3. ✅ Referral ID Format

**Status**: पहले से ही सही format में है! ✓

**Current Implementation**:
- Format: `REF-XXXXXX` (6 uppercase hex characters)
- Example: `REF-574241`, `REF-A1B2C3`
- Unique guarantee के साथ generate होता है
- Code location: `src/Core/Auth.php`

---

## 📋 कैसे Use करें

### Step 1: Payment Migration चलाएं

```bash
# XAMPP के htdocs folder में जाएं
cd c:\xampp\htdocs\tcm\tcm-2.0

# Migration script run करें
php migrate_referral_columns.php
```

**Success दिखना चाहिए**:
```
✅ Migration completed successfully!

📊 Current table structure:
   - referral_code: varchar(50) (NULL)
   - referrer_id: bigint unsigned (NULL)

🎉 Payment submission form can now accept referral codes!
```

### Step 2: Payment Test करें

1. Student login करें
2. `/student/payments/submit` पर जाएं
3. Form भरें और referral code डालें (जैसे `REF-574241`)
4. Submit करें - अब 500 error नहीं आना चाहिए

### Step 3: Daily Tasks Test करें

**Manual Testing**:
```bash
# सभी students के लिए tasks generate करें
php cron-daily-tasks.php
```

**किसी specific student के लिए test करें**:
```php
require 'src/bootstrap.php';
use TCM\Models\DailyTask;

// Student ID 1 के लिए tasks generate करो
$tasks = DailyTask::generateForStudent(1);
print_r($tasks);
```

### Step 4: Referral System Verify करें

**Existing referral IDs check करें**:
```sql
SELECT id, name, referral_id FROM users WHERE referral_id IS NOT NULL;
```

**Referral code से payment check करें**:
```sql
SELECT id, user_id, item_title, referral_code, referrer_id 
FROM payment_submissions 
WHERE referral_code IS NOT NULL;
```

---

## 🔄 Daily Tasks अब कैसे काम करते हैं

### Task Generation का Flow

1. **Cron Trigger** (हर Friday दोपहर 12 बजे):
   ```
   php cron-daily-tasks.php
   ```

2. **Progress Analysis**:
   - Enrolled courses fetch करो
   - Per course completed lessons count करो
   - Next incomplete lessons identify करो
   - Recent completions देखो

3. **AI Generation** (Primary Method):
   ```
   - Input: Student data + Course progress + Next lessons list
   - AI Process: Completed lessons देखकर practice problems सोचो
   - Output: 3-5 specific practice problems/challenges
   ```

4. **Fallback** (अगर AI fail हो):
   ```
   - Progress data use करके smart tasks बनाओ
   - Incomplete courses: Next lesson के लिए practice
   - Completed courses: Project-based challenges
   ```

### Example Scenarios

**Scenario 1: Beginner (10% complete)**
```
✅ Completed: "HTML Basics", "CSS Introduction"
📝 Task Generated:
   "HTML & CSS से Personal Profile Card बनाओ
    Semantic HTML tags और basic CSS styling use करो जो पहले 2 lessons में सीखा।
    Difficulty: Easy | 30 mins"
```

**Scenario 2: Intermediate (50% complete)**
```
✅ Completed: JavaScript basics, functions, arrays
📝 Next Lessons: Objects, DOM manipulation
📝 Task Generated:
   "Contact List App बनाओ
    Arrays और functions use करके contacts manage करो। Search feature add करो।
    Difficulty: Medium | 45 mins"
```

**Scenario 3: Advanced (100% complete)**
```
✅ Completed: पूरा JavaScript course
📝 Task Generated:
   "Weather Dashboard बनाओ API use करके
    सारे concepts apply करो: async/await, DOM, error handling.
    Difficulty: Hard | 90 mins"
```

---

## 🧪 Testing Checklist

### Payment System
- [ ] Migration successfully run हो गया
- [ ] Payment form referral code accept करता है
- [ ] Invalid referral code पर proper error दिखता है
- [ ] Valid referral पर wallet credit होता है (approval के बाद)
- [ ] Payment history में referral info दिखता है

### Daily Tasks
- [ ] Tasks completed lessons के basis पर बनते हैं
- [ ] Task description में next lessons mention होते हैं
- [ ] Difficulty progress level के हिसाब से है
- [ ] Completed courses को project tasks मिलते हैं
- [ ] AI unavailable होने पर fallback काम करता है

### Referral System
- [ ] नए users को REF-XXXXXX format में ID मिलता है
- [ ] Referral codes students search कर सकते हैं
- [ ] Payment approval पर referrer का wallet credit होता है
- [ ] Referral chain database में track होता है

---

## 🐛 अगर Problem हो तो

### Payment Error अब भी आ रहा है

**Check 1**: Columns add हुए कि नहीं
```sql
DESCRIBE payment_submissions;
-- देखना चाहिए: referral_code, referrer_id columns
```

**Check 2**: Error logs देखें
```bash
# XAMPP Windows
type C:\xampp\apache\logs\error.log | findstr payment
```

**Check 3**: Debug mode ON करें
```php
// .env file में
APP_DEBUG=true

// अब payment submit करो और detailed error देखो
```

### Tasks Generate नहीं हो रहे

**Check 1**: OpenRouter API key check करें
```bash
# .env file में
OPENROUTER_API_KEY=your_actual_key_here
```

**Check 2**: Manually generate करके देखें
```bash
php -r "require 'src/bootstrap.php'; var_dump(TCM\Models\DailyTask::generateForStudent(1));"
```

**Check 3**: Database tables exist करते हैं
```sql
-- Check करें कि ये tables हैं
SHOW TABLES LIKE 'lesson_progress';
SHOW TABLES LIKE 'course_lessons';
SHOW TABLES LIKE 'daily_tasks';
```

### Referral काम नहीं कर रहा

**Check 1**: User का referral ID है कि नहीं
```sql
SELECT id, name, referral_id FROM users WHERE id = YOUR_USER_ID;
-- अगर NULL है तो generate करो
```

**Check 2**: Referral code valid है
```sql
-- Isse referrer user milna चाहिए
SELECT id, name FROM users WHERE referral_id = 'REF-574241';
```

---

## 💡 पहले vs अब में Difference

| Feature | पहले | अब |
|---------|------|-----|
| **Payment Error** | 500 error आता था | Smooth काम करता है |
| **Task Type** | Generic "complete lesson" | Specific practice problems |
| **Progress Track** | सिर्फ % | Lesson-level detail |
| **Task Quality** | Low (generic) | High (personalized) |
| **AI Context** | Basic course list | Full progress + next steps |

---

## 📁 कौन सी Files बदली हैं

### Core Changes
1. **`src/Models/DailyTask.php`**
   - `getStudentProgress()` - lesson tracking add की
   - `getNextLessons()` - नया method
   - `createFallbackTasks()` - smart logic

2. **`src/Services/OpenRouterService.php`**
   - AI prompt improve किया
   - Practice problems focus
   - Better progress context

3. **`database/add_referral_to_payments.sql`**
   - Idempotent बनाया (multiple times run safe)

### New Files
1. **`migrate_referral_columns.php`**
   - Automatic migration
   - Safe और clear messages

2. **`FIXED_ISSUES_SUMMARY.md`** (English)
3. **`FIX_GUIDE_HINDI.md`** (यह file)

---

## 🚀 Production में कैसे Deploy करें

### 1. Database Backup लें (जरूरी!)
```bash
mysqldump -u root -p tcm_db > backup_before_fix.sql
```

### 2. Migration चलाएं
```bash
cd /path/to/tcm-2.0
php migrate_referral_columns.php
```

### 3. Verify करें
```sql
-- Check करें columns add हुए
DESCRIBE payment_submissions;

-- Check करें data सही है
SELECT COUNT(*) FROM payment_submissions;
```

### 4. Cron Job Setup (अगर नहीं है)
```bash
crontab -e

# Add this line (हर Friday 12 PM)
0 12 * * 5 cd /path/to/tcm-2.0 && php cron-daily-tasks.php
```

---

## ✅ Success Criteria

System सही से काम कर रहा है अगर:

1. ✅ Payment form referral code accept करता है बिना error के
2. ✅ Tasks में specific practice problems दिखते हैं
3. ✅ Tasks में completed और next lessons का reference होता है
4. ✅ Referral wallet credit होता है payment approval पर
5. ✅ Cron job हर Friday tasks generate करता है

---

## 📞 Help चाहिए तो

1. Error logs check करें: `C:\xampp\apache\logs\error.log`
2. Debug mode ON करें `.env` में: `APP_DEBUG=true`
3. Migration फिर से run करें: `php migrate_referral_columns.php`
4. Database manually check करें phpMyAdmin से

---

## 🎉 Summary

**3 Main Fixes**:
1. ✅ Payment error fix (referral columns add किए)
2. ✅ Daily tasks smart बनाए (progress-based practice problems)
3. ✅ Referral system already correct format में था

**Next Steps**:
1. Migration run करो: `php migrate_referral_columns.php`
2. Test करो payment submission
3. Test करो daily tasks: `php cron-daily-tasks.php`

**Results**:
- Students को अब specific practice problems मिलेंगे
- Payment submission smoothly काम करेगा referral code के साथ
- System ज्यादा personalized और useful हो गया!

---

**अंतिम Update**: June 19, 2026  
**Version**: TCM 2.0  
**Status**: ✅ Testing के लिए Ready
