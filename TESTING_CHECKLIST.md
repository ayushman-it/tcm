# 🧪 Testing Checklist - OpenRouter Integration

## ✅ Integration Complete - Now Test Everything

---

## 📝 Pre-Test Verification

### 1. Check Files Are Uploaded ✅
```
✓ src/Services/OpenRouterAI.php
✓ src/Services/AIContentGenerator.php  
✓ src/Controllers/Student/AgentController.php
✓ .env (with production API key)
```

### 2. Run Connection Test
**URL:** `https://thecodemunk.in/test-openrouter.php`

**Expected:**
- ✅ OPENROUTER_API_KEY found
- ✅ Connection test succeeds
- ✅ Chat test returns responses
- ✅ Lesson content test generates JSON

---

## 🎯 Test 1: TCM Agent (Priority 1)

### Location
Student Dashboard → TCM Agent icon (bottom right)

### Test Queries
Send these messages one by one:

#### Test 1.1: Simple Greeting
```
Message: "hello"
Expected: Friendly response introducing TCM Agent
```

#### Test 1.2: Teach Me (CRITICAL)
```
Message: "teach me how to write a class"
Expected: 
- Complete class code (15+ lines)
- Explanation in Hinglish
- Example with properties and methods
- NO placeholders like "// code here"
```

#### Test 1.3: How To (CRITICAL)
```
Message: "how to write html code"
Expected:
- Complete HTML example
- Explanation of structure
- Real working code
```

#### Test 1.4: Code Generation
```
Message: "write a function to add two numbers in javascript"
Expected:
- Complete function code
- Example usage
- Output explanation
```

#### Test 1.5: Hindi/Hinglish
```
Message: "javascript me loop kaise likhte hai"
Expected:
- Response in Hinglish
- Code examples with for, while loops
- Detailed explanation
```

---

## 📚 Test 2: Lesson Content Auto-Generation (Priority 1)

### Location
Student Dashboard → Courses → Course Tracking Section

### Steps

#### 2.1 Expand a Course
1. Click on any enrolled course (e.g., "React.js – Modern Frontend")
2. Course should expand showing lessons

#### 2.2 Expand a Lesson (CRITICAL TEST)
1. Click on any lesson (e.g., "React Router – Navigation & Pages")
2. Wait 3-5 seconds (API call happening)
3. Concepts should auto-generate and display

#### 2.3 Check Content Quality

**✅ GOOD CONTENT:**
- Detailed overview in Hindi + English
- Key concepts with explanations (100+ words each)
- REAL code examples (20+ lines)
- Specific to the lesson topic
- Actual code that can be copied and run

**❌ BAD CONTENT (Report if you see):**
- "Follow course materials"
- "Code examples available"
- "Practice with examples"
- Generic advice
- Placeholder comments like `// Code here`
- Short code snippets (< 10 lines)

#### 2.4 Test Multiple Lessons
Try expanding 3-4 different lessons:
- One from React.js
- One from JavaScript
- One from HTML/CSS
- One from Backend

Check each for content quality.

---

## 🔍 Test 3: Error Handling

### 3.1 TCM Agent - Error Scenario
1. Disconnect internet briefly
2. Try sending a message
3. **Expected:** Fallback response (rule-based)
4. Reconnect and try again
5. **Expected:** AI response works

### 3.2 Lesson Generation - Already Cached
1. Expand a lesson you've already opened before
2. **Expected:** Instant load (from cache)
3. Content should be same as before
4. No new API call needed

---

## 📊 Test 4: Performance Check

### 4.1 TCM Agent Response Time
- First message: 3-5 seconds ✅ (acceptable)
- Subsequent messages: 2-4 seconds ✅
- Over 10 seconds: ❌ (report issue)

### 4.2 Lesson Content Generation Time
- First time: 5-10 seconds ✅ (acceptable)
- Cached: < 1 second ✅
- Over 15 seconds: ❌ (report issue)

---

## 🐛 Common Issues & Solutions

### Issue 1: TCM Agent returns "Sorry, I encountered an error"
**Causes:**
- API key invalid
- API rate limit hit
- Network issue

**Fix:**
1. Check error logs
2. Verify .env has correct key
3. Test with test-openrouter.php

### Issue 2: Lesson content shows generic text
**Causes:**
- Old cached content
- AI prompt not working

**Fix:**
```sql
-- Clear cache and regenerate
TRUNCATE TABLE lesson_content;
```

### Issue 3: 500 Server Error
**Causes:**
- PHP errors
- Missing files
- Database connection issue

**Fix:**
1. Check PHP error logs
2. Verify all files uploaded
3. Check database connection

### Issue 4: Content not appearing
**Causes:**
- JavaScript error
- API endpoint not responding

**Fix:**
1. Open browser console (F12)
2. Check for JavaScript errors
3. Look at Network tab for failed requests

---

## 📸 Screenshot Checklist

Take screenshots of:

1. ✅ test-openrouter.php - All tests passing
2. ✅ TCM Agent - "teach me" response with code
3. ✅ Lesson expanded - Showing generated concepts
4. ✅ Code example from lesson - Full, detailed code
5. ❌ Any errors encountered

---

## 🎯 Success Criteria

### Must Have (Critical):
- [ ] TCM Agent responds to "teach me" with REAL code
- [ ] Lesson content has NO generic placeholders
- [ ] Code examples are 15+ lines minimum
- [ ] No "Follow course materials" text anywhere
- [ ] Hindi + English bilingual content

### Nice to Have:
- [ ] Responses within 5 seconds
- [ ] Hinglish explanations working
- [ ] Code is properly formatted
- [ ] Examples are beginner-friendly

---

## 📝 Report Template

If issues found, report like this:

```
**Issue:** TCM Agent giving generic response
**Test Query:** "teach me how to write a class"
**Expected:** Complete class code with explanation
**Actual:** "Please refer to course materials"
**Screenshot:** [attach]
**Browser:** Chrome/Firefox/Safari
**Time:** [when it happened]
```

---

## ✅ Final Checklist

Before marking as complete:

- [ ] Ran test-openrouter.php - All green
- [ ] Tested TCM Agent - 5 different queries
- [ ] Tested Lesson Generation - 3+ lessons
- [ ] Verified NO generic placeholders
- [ ] Checked code quality - Real, working code
- [ ] Tested on production (thecodemunk.in)
- [ ] Took screenshots of working features

---

## 🚀 Next Steps After Testing

### If All Tests Pass:
1. ✅ Mark integration as complete
2. ✅ Monitor for 24 hours
3. ✅ Collect student feedback
4. ✅ Consider removing old Gemini files

### If Issues Found:
1. 🐛 Document the issue
2. 🐛 Provide screenshots
3. 🐛 Share error logs
4. 🐛 We'll fix immediately

---

**Testing Date:** _____________  
**Tester:** _____________  
**Status:** ⏳ Pending / ✅ Passed / ❌ Issues Found  

**Notes:**
_________________________________
_________________________________
_________________________________
