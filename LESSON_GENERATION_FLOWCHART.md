# 📊 Lesson Content Generation - Visual Flowchart

## 🎯 Complete System Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                         ADMIN PANEL UI                          │
│                  (views/admin/ai-content-generator.php)         │
│                                                                 │
│  [Select Course ▼] [Select Module ▼] [Language: hi+en ▼]      │
│                                                                 │
│  Module: JavaScript Basics                                      │
│  ├─ Lesson 1: Introduction to JS      [Generate Content]       │
│  ├─ Lesson 2: Variables               [Generate Content]       │
│  ├─ Lesson 3: Data Types              [Generate Content]       │
│  └─ [Generate All Lessons in Module]                           │
└──────────────────────┬──────────────────────────────────────────┘
                       │ Button Click
                       │ POST Request
                       ↓
┌─────────────────────────────────────────────────────────────────┐
│                    AIContentController.php                      │
│              (src/Controllers/Admin/)                           │
│                                                                 │
│  generateLesson() {                                             │
│    1. Validate lesson_id                                        │
│    2. Call AIContentGenerator                                   │
│    3. Return JSON response                                      │
│  }                                                              │
└──────────────────────┬──────────────────────────────────────────┘
                       │ Method Call
                       ↓
┌─────────────────────────────────────────────────────────────────┐
│                   AIContentGenerator.php                        │
│                 (src/Services/)                                 │
│                                                                 │
│  generateLessonContent($lessonId, $language) {                  │
│    ┌──────────────────────────────────────────────┐            │
│    │ STEP 1: Fetch Lesson Details                 │            │
│    │  SELECT l.*, m.title, c.title                │            │
│    │  FROM course_lessons l                       │            │
│    │  JOIN course_modules m                       │            │
│    │  JOIN courses c                              │            │
│    └─────────────────┬────────────────────────────┘            │
│                      ↓                                          │
│    ┌──────────────────────────────────────────────┐            │
│    │ STEP 2: Build AI Prompt                     │            │
│    │  - Lesson title, module, course             │            │
│    │  - Duration, type                           │            │
│    │  - Language instructions                    │            │
│    │  - JSON structure requirements              │            │
│    └─────────────────┬────────────────────────────┘            │
│                      ↓                                          │
│    ┌──────────────────────────────────────────────┐            │
│    │ STEP 3: Call AI API                         │            │
│    │  if (GEMINI_API_KEY exists)                 │            │
│    │    → callGemini($prompt)                    │            │
│    │  else if (OPENAI_API_KEY exists)            │            │
│    │    → callOpenAI($prompt)                    │            │
│    └─────────────────┬────────────────────────────┘            │
│                      ↓                                          │
│    ┌──────────────────────────────────────────────┐            │
│    │ STEP 4: Parse AI Response                   │            │
│    │  - JSON decode                              │            │
│    │  - Validate required fields                 │            │
│    │  - Structure content array                  │            │
│    └─────────────────┬────────────────────────────┘            │
│                      ↓                                          │
│    ┌──────────────────────────────────────────────┐            │
│    │ STEP 5: Save to Database                    │            │
│    │  INSERT INTO lesson_content                 │            │
│    │  ON DUPLICATE KEY UPDATE                    │            │
│    └─────────────────┬────────────────────────────┘            │
│                      ↓                                          │
│    return $content;                                             │
│  }                                                              │
└──────────────────────┬──────────────────────────────────────────┘
                       │ API Call
                       ↓
┌─────────────────────────────────────────────────────────────────┐
│                      GOOGLE GEMINI API                          │
│              (generativelanguage.googleapis.com)                │
│                                                                 │
│  POST /v1beta/models/gemini-1.5-pro:generateContent             │
│  {                                                              │
│    "contents": [{                                               │
│      "parts": [{ "text": "Your prompt here..." }]              │
│    }],                                                          │
│    "generationConfig": {                                        │
│      "temperature": 0.7,                                        │
│      "maxOutputTokens": 4000,                                   │
│      "responseMimeType": "application/json"                     │
│    }                                                            │
│  }                                                              │
│                                                                 │
│  ⚙️ AI Processing (30-60 seconds)                              │
│     - Understands lesson topic                                  │
│     - Generates explanations                                    │
│     - Creates code examples                                     │
│     - Designs exercises                                         │
│     - Finds resources                                           │
│                                                                 │
│  Response:                                                      │
│  {                                                              │
│    "candidates": [{                                             │
│      "content": {                                               │
│        "parts": [{                                              │
│          "text": "{ JSON content here... }"                     │
│        }]                                                       │
│      }                                                          │
│    }]                                                           │
│  }                                                              │
└──────────────────────┬──────────────────────────────────────────┘
                       │ JSON Response
                       ↓
┌─────────────────────────────────────────────────────────────────┐
│                      MYSQL DATABASE                             │
│                  Table: lesson_content                          │
│                                                                 │
│  ┌──────────────────────────────────────────────────┐          │
│  │ id: 123                                           │          │
│  │ lesson_id: 45                                     │          │
│  │ overview_hi: "Is lesson mein..."                 │          │
│  │ overview_en: "In this lesson..."                 │          │
│  │ key_concepts: [JSON array]                       │          │
│  │ explanation_hi: "Variables ek..."                │          │
│  │ explanation_en: "Variables are..."               │          │
│  │ code_examples: [JSON array]                      │          │
│  │ exercises: [JSON array]                          │          │
│  │ resources: [JSON array]                          │          │
│  │ estimated_time: 45                               │          │
│  │ language: "hi+en"                                │          │
│  │ ai_generated: 1                                  │          │
│  │ ai_model: "gemini:gemini-1.5-pro"               │          │
│  │ status: "draft"                                  │          │
│  │ generated_at: "2026-06-20 10:30:00"             │          │
│  └──────────────────────────────────────────────────┘          │
└──────────────────────┬──────────────────────────────────────────┘
                       │ Data Saved
                       ↓
┌─────────────────────────────────────────────────────────────────┐
│                      ADMIN REVIEW UI                            │
│                                                                 │
│  ✅ Content Generated Successfully!                            │
│                                                                 │
│  Preview:                                                       │
│  ┌─────────────────────────────────────────────────┐           │
│  │ Overview (Hindi):                                │           │
│  │ Is lesson mein hum JavaScript variables ke...   │           │
│  │                                                  │           │
│  │ Key Concepts:                                    │           │
│  │ • Variables kya hain                            │           │
│  │ • let, const, var                               │           │
│  │                                                  │           │
│  │ Code Examples: [View]                           │           │
│  │ Exercises: [View]                               │           │
│  └─────────────────────────────────────────────────┘           │
│                                                                 │
│  [Edit Content]  [Regenerate]  [Publish]                       │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Parallel Processing (Module Generation)

```
Admin Clicks "Generate All Lessons"
        ↓
┌───────────────────────────────────────────┐
│    AIContentController::generateModule()   │
│                                            │
│    foreach ($lessons as $lesson) {         │
│      try {                                 │
│        generateLessonContent($lesson)      │
│        $results[] = ['success' => true]    │
│      } catch (Exception $e) {              │
│        $results[] = ['error' => $e]        │
│      }                                     │
│    }                                       │
└─────────────┬─────────────────────────────┘
              │
              ├──→ Lesson 1 → Gemini API → Save → ✅
              │
              ├──→ Lesson 2 → Gemini API → Save → ✅
              │
              ├──→ Lesson 3 → Gemini API → Save → ❌ (Error)
              │
              └──→ Lesson 4 → Gemini API → Save → ✅
              
Results:
{
  "45": { "success": true },
  "46": { "success": true },
  "47": { "success": false, "error": "API timeout" },
  "48": { "success": true }
}
```

---

## 🎯 Content Structure (JSON Format)

```json
{
  "overview_hi": "2-3 sentences in Hindi",
  "overview_en": "2-3 sentences in English",
  
  "key_concepts": [
    {
      "title_hi": "Concept Hindi title",
      "title_en": "Concept English title",
      "explanation_hi": "Detailed Hindi explanation",
      "explanation_en": "Detailed English explanation",
      "importance": "Why this matters"
    }
  ],
  
  "explanation_hi": "# Main Heading\n\nDetailed content...",
  "explanation_en": "# Main Heading\n\nDetailed content...",
  
  "code_examples": [
    {
      "title": "Example Title",
      "description_hi": "Hindi description",
      "description_en": "English description",
      "code": "let x = 10;\nconsole.log(x);",
      "output": "10",
      "explanation_hi": "Hindi explanation",
      "explanation_en": "English explanation"
    }
  ],
  
  "exercises": [
    {
      "title": "Exercise Title",
      "difficulty": "beginner|intermediate|advanced",
      "description_hi": "Hindi description",
      "description_en": "English description",
      "starter_code": "// Start here\n",
      "hints": ["Hint 1", "Hint 2"],
      "solution": "Complete code",
      "explanation": "How to solve"
    }
  ],
  
  "resources": [
    {
      "type": "documentation|article|video|tool",
      "title": "Resource Title",
      "url": "https://...",
      "description_hi": "Hindi description",
      "description_en": "English description"
    }
  ],
  
  "estimated_time": 45
}
```

---

## 🔌 API Integration Flow

### Gemini API Call:

```
┌──────────────────────┐
│  AIContentGenerator  │
│   callGemini()       │
└──────────┬───────────┘
           │
           ↓ Build Request
┌──────────────────────┐
│   cURL Request       │
│   POST with JSON     │
│   + API Key in URL   │
└──────────┬───────────┘
           │
           ↓ Send
┌──────────────────────────────────┐
│  Gemini API Server               │
│  - Validate API key              │
│  - Process prompt                │
│  - Generate content (30-60s)     │
│  - Return JSON response          │
└──────────┬───────────────────────┘
           │
           ↓ Receive
┌──────────────────────┐
│  Parse Response      │
│  - Decode JSON       │
│  - Extract text      │
│  - Validate format   │
└──────────┬───────────┘
           │
           ↓
      Return Content
```

---

## 🎓 Student View (How Content is Displayed)

```
┌────────────────────────────────────────────────┐
│           STUDENT LESSON VIEW                   │
│                                                 │
│  📚 Lesson: JavaScript Variables                │
│                                                 │
│  [हिंदी] [English] [Both] ← Language Toggle    │
│                                                 │
│  ───────────────────────────────────────────    │
│                                                 │
│  📖 Overview                                    │
│  Is lesson mein hum JavaScript variables ke     │
│  baare mein seekhenge...                        │
│                                                 │
│  🎯 Key Concepts                                │
│  • Variables kya hain                          │
│  • let, const, var mein difference             │
│  • Variable naming rules                       │
│                                                 │
│  📝 Detailed Explanation                        │
│  [Markdown rendered content]                    │
│                                                 │
│  💻 Code Examples                               │
│  ┌─────────────────────────────────────┐       │
│  │ let name = 'Rahul';                 │       │
│  │ const age = 25;                     │       │
│  │ console.log(name, age);             │       │
│  │                                     │       │
│  │ Output: Rahul 25                    │       │
│  └─────────────────────────────────────┘       │
│  [Copy Code] [Run in Playground]               │
│                                                 │
│  ✍️ Practice Exercises                          │
│  Exercise 1: Create User Profile (Beginner)    │
│  [View Details] [Start Exercise]               │
│                                                 │
│  📚 Additional Resources                        │
│  • MDN Documentation                           │
│  • JavaScript.info Tutorial                    │
│  • FreeCodeCamp Video                          │
│                                                 │
│  [Mark as Complete] [Next Lesson →]            │
└────────────────────────────────────────────────┘
```

---

## ⚡ Performance Optimization Flow

```
┌────────────────────────────────────────┐
│    High Volume Generation Request      │
│    (100+ lessons)                      │
└──────────────┬─────────────────────────┘
               │
               ↓
┌────────────────────────────────────────┐
│    Add to Job Queue                    │
│    (Redis/Database)                    │
│                                        │
│    Jobs Table:                         │
│    ├─ lesson_id: 1   status: pending   │
│    ├─ lesson_id: 2   status: pending   │
│    ├─ lesson_id: 3   status: pending   │
│    └─ ...                              │
└──────────────┬─────────────────────────┘
               │
               ↓
┌────────────────────────────────────────┐
│    Background Workers (3 processes)    │
│                                        │
│    Worker 1 ─→ Process Job 1          │
│    Worker 2 ─→ Process Job 2          │
│    Worker 3 ─→ Process Job 3          │
│                                        │
│    Each with rate limiting:            │
│    - Max 10 requests/minute            │
│    - 2 second delay between calls      │
└──────────────┬─────────────────────────┘
               │
               ↓
┌────────────────────────────────────────┐
│    Progress Tracking                   │
│    - Update job status                 │
│    - Log errors                        │
│    - Notify admin when complete        │
└────────────────────────────────────────┘
```

---

## 🔒 Error Handling Flow

```
User Clicks Generate
        ↓
┌───────────────────┐
│  Try Generation   │
└─────────┬─────────┘
          │
    ┌─────┴─────┐
    │           │
   OK         ERROR
    │           │
    ↓           ↓
  Save      ┌────────────────┐
  to DB     │  Error Type?   │
    │       └────┬────┬──────┘
    │            │    │
    │         API  Timeout  Parse
    │        Error   │      Error
    │            │   │        │
    ↓            ↓   ↓        ↓
  Return    Log   Retry   Show
  Success   Error  (3x)   Error
    │            │   │        │
    │            └───┴────────┘
    │                 │
    ↓                 ↓
  Display         Display
  Content         Error
                  Message
```

---

## 🎉 Quick Reference

### Files to Know:
```
src/Services/AIContentGenerator.php    ← Core logic
src/Controllers/Admin/AIContentController.php  ← Admin API
views/admin/ai-content-generator.php   ← UI
database/lesson_content.sql            ← Database schema
.env                                   ← API keys
```

### Key Functions:
```
generateLessonContent()  → Single lesson
generateForModule()      → All lessons in module
getContent()             → Retrieve content
regenerateContent()      → Regenerate existing
```

### Important Routes:
```
/admin/ai-content                      → Main UI
/admin/ai-content/generate-lesson      → Generate
/admin/ai-content/generate-module      → Batch generate
/admin/ai-content/publish              → Publish content
```

---

**Pro Tip:** Save this flowchart for quick reference! 🚀
