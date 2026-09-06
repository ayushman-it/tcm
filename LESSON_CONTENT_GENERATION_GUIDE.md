# 📚 Lesson Content Generation System - Complete Guide

## 🎯 Overview

Yeh system **Gemini AI** ka use karke automatically **high-quality lesson content** generate karta hai. Admin panel se ek button click se:
- **Detailed explanations** (Hindi + English)
- **Working code examples**
- **Practice exercises**
- **Resources & references**

Sab kuch AI automatically generate kar deta hai! 🚀

---

## 🏗️ System Architecture

```
┌─────────────────┐
│   Admin Panel   │
│  (Web UI)       │
└────────┬────────┘
         │
         │ HTTP POST
         ↓
┌─────────────────────────────┐
│  AIContentController.php    │
│  (Request Handler)          │
└─────────┬───────────────────┘
          │
          │ Method Call
          ↓
┌─────────────────────────────┐
│  AIContentGenerator.php     │
│  (Business Logic)           │
└─────────┬───────────────────┘
          │
          │ API Request
          ↓
┌─────────────────────────────┐
│   Google Gemini API         │
│   (AI Model)                │
└─────────┬───────────────────┘
          │
          │ JSON Response
          ↓
┌─────────────────────────────┐
│   lesson_content table      │
│   (MySQL Database)          │
└─────────────────────────────┘
```

---

## 🔧 Main Components

### 1️⃣ **AIContentGenerator.php** (Core Service)

**Location:** `src/Services/AIContentGenerator.php`

**Key Features:**
- ✅ Gemini & OpenAI support (auto-detects API key)
- ✅ Bilingual content (Hindi + English)
- ✅ Structured JSON output
- ✅ Database storage
- ✅ Error handling

**Main Methods:**

#### `generateLessonContent($lessonId, $language)`
```php
// Generate content for single lesson
$generator = new AIContentGenerator($db);
$content = $generator->generateLessonContent(123, 'hi+en');
```

**Steps:**
1. Lesson details fetch karta hai (title, module, course)
2. AI-ready prompt build karta hai
3. Gemini API call karta hai
4. Response ko parse & validate karta hai
5. Database mein save karta hai

#### `generateForModule($moduleId, $language)`
```php
// Generate content for all lessons in module
$results = $generator->generateForModule(45, 'hi+en');
```

**Returns:** Array with success/error for each lesson

#### `getContent($lessonId, $preferredLanguage)`
```php
// Retrieve existing content from database
$content = $generator->getContent(123, 'hi');
```

---

### 2️⃣ **AIContentController.php** (Admin API)

**Location:** `src/Controllers/Admin/AIContentController.php`

**Endpoints:**

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/admin/ai-content` | Show generator UI |
| `POST` | `/admin/ai-content/generate-lesson` | Generate single lesson |
| `POST` | `/admin/ai-content/generate-module` | Generate all lessons in module |
| `GET` | `/admin/ai-content/lesson/{id}` | Get lesson content |
| `POST` | `/admin/ai-content/regenerate-lesson` | Regenerate content |
| `POST` | `/admin/ai-content/publish` | Publish/approve content |
| `GET` | `/admin/ai-content/status` | Get all lessons status |

#### Example API Request:
```javascript
// Generate content for lesson
fetch('/admin/ai-content/generate-lesson', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        lesson_id: 123,
        language: 'hi+en'
    })
})
.then(res => res.json())
.then(data => console.log(data.content));
```

---

### 3️⃣ **Database Schema** (`lesson_content` table)

```sql
CREATE TABLE lesson_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT NOT NULL,
    
    -- Overview
    overview_hi TEXT,
    overview_en TEXT,
    
    -- Main Content
    key_concepts JSON,
    explanation_hi LONGTEXT,
    explanation_en LONGTEXT,
    
    -- Code & Exercises
    code_examples JSON,
    exercises JSON,
    resources JSON,
    
    -- Metadata
    estimated_time INT,
    language VARCHAR(10),
    ai_generated BOOLEAN DEFAULT 1,
    ai_model VARCHAR(50),
    status ENUM('draft', 'published') DEFAULT 'draft',
    
    -- Timestamps
    generated_at DATETIME,
    reviewed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY (lesson_id),
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id)
);
```

---

## 🤖 AI Prompt Structure

### Prompt ka Format:

```
You are an expert programming instructor. Generate content for:

**Lesson:** Variables in JavaScript
**Module:** JavaScript Basics
**Course:** Web Development Fundamentals
**Type:** video
**Duration:** 45 minutes

Provide content in both Hindi and English.

Generate JSON with:
- overview_hi & overview_en (2-3 sentences)
- key_concepts (array of concepts with explanations)
- explanation_hi & explanation_en (detailed markdown)
- code_examples (working code with output)
- exercises (progressively challenging)
- resources (documentation, articles, videos)
- estimated_time
```

### AI Response Example:

```json
{
  "overview_hi": "Is lesson mein hum JavaScript variables ke baare mein seekhenge...",
  "overview_en": "In this lesson, we'll learn about JavaScript variables...",
  
  "key_concepts": [
    {
      "title_hi": "Variable Declaration",
      "title_en": "Variable Declaration",
      "explanation_hi": "Variable ek container hai jo data store karta hai...",
      "explanation_en": "A variable is a container that stores data...",
      "importance": "Variables are fundamental to programming"
    }
  ],
  
  "code_examples": [
    {
      "title": "Declaring Variables",
      "description_hi": "Yeh example dikhata hai kaise variables declare karte hain",
      "description_en": "This example shows how to declare variables",
      "code": "let name = 'Rahul';\nconst age = 25;\nvar city = 'Mumbai';",
      "output": "// name = 'Rahul', age = 25, city = 'Mumbai'",
      "explanation_hi": "let aur const modern ways hain...",
      "explanation_en": "let and const are modern ways..."
    }
  ],
  
  "exercises": [
    {
      "title": "Create a User Profile",
      "difficulty": "beginner",
      "description_hi": "Ek user profile banao with name, age, city",
      "description_en": "Create a user profile with name, age, city",
      "starter_code": "// Create variables for user profile\n",
      "hints": ["Use const for values that won't change"],
      "solution": "const name = 'Priya';\nconst age = 22;\nlet city = 'Delhi';",
      "explanation": "Used const for name and age, let for city..."
    }
  ],
  
  "resources": [
    {
      "type": "documentation",
      "title": "MDN - Variables",
      "url": "https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Grammar_and_types#declarations",
      "description_hi": "Mozilla ka official documentation",
      "description_en": "Mozilla's official documentation"
    }
  ],
  
  "estimated_time": 45
}
```

---

## 🚀 How to Use (Step-by-Step)

### **Method 1: Admin Panel UI** (Recommended)

1. **Open Admin Panel:**
   ```
   http://localhost/tcm/tcm-2.0/admin/ai-content
   ```

2. **Select Course & Module:**
   - Dropdown se course select karo
   - Module select karo

3. **Generate Content:**
   - **Single Lesson:** Click "Generate Content" button on lesson
   - **Entire Module:** Click "Generate All" button on module

4. **Select Language:**
   - `hi+en` - Both languages (default)
   - `hi` - Hindi only
   - `en` - English only

5. **Wait for Generation:**
   - Progress bar dikhega
   - 30-60 seconds lag sakta hai

6. **Review & Publish:**
   - Generated content preview karo
   - "Publish" button se approve karo

---

### **Method 2: Command Line** (For Bulk Operations)

```bash
# Generate content for specific lesson
php generate-content.php --lesson=123 --lang=hi+en

# Generate for entire module
php generate-content.php --module=45 --lang=hi+en

# Regenerate existing content
php generate-content.php --lesson=123 --regenerate

# Generate for all courses
php generate-content.php --all --lang=hi+en
```

**Options:**
- `--lesson=ID` - Specific lesson
- `--module=ID` - All lessons in module
- `--course=ID` - All lessons in course
- `--all` - All lessons in database
- `--lang=hi|en|hi+en` - Language preference
- `--regenerate` - Overwrite existing content

---

### **Method 3: Programmatic (PHP Code)**

```php
<?php
require_once 'config/config.php';
use TCM\Services\AIContentGenerator;

$db = getDbConnection();
$generator = new AIContentGenerator($db);

// Generate for single lesson
try {
    $content = $generator->generateLessonContent(123, 'hi+en');
    echo "Content generated successfully!\n";
    print_r($content);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Generate for module
$results = $generator->generateForModule(45, 'hi+en');
foreach ($results as $lessonId => $result) {
    if ($result['success']) {
        echo "Lesson $lessonId: Success\n";
    } else {
        echo "Lesson $lessonId: Failed - {$result['error']}\n";
    }
}

// Retrieve content
$content = $generator->getContent(123, 'hi');
if ($content) {
    echo "Overview: " . $content['overview_hi'] . "\n";
}
?>
```

---

## 🔑 Configuration

### **1. Environment Variables**

Update `.env` file:

```env
# Google Gemini (FREE - Recommended)
GEMINI_API_KEY=AIzaSyBDcFdVwbhai...

# OR OpenAI (Paid)
OPENAI_API_KEY=sk-proj-...
```

**Priority:** System automatically uses Gemini if key exists, otherwise falls back to OpenAI.

### **2. API Settings**

Modify in `AIContentGenerator.php`:

```php
// Gemini Settings
$this->model = 'gemini-1.5-pro';  // or 'gemini-1.5-flash'
$this->temperature = 0.7;         // Creativity (0-1)
$this->maxTokens = 4000;          // Max response length

// OpenAI Settings
$this->model = 'gpt-4o';          // or 'gpt-4', 'gpt-3.5-turbo'
```

---

## 📊 Content Quality Guidelines

AI generates content following these standards:

### ✅ **Code Examples:**
- Real, working code (not pseudo-code)
- Modern syntax (ES6+ for JavaScript)
- Indian context (names, examples)
- Includes output/expected results
- Line-by-line explanations

### ✅ **Exercises:**
- Progressive difficulty (beginner → advanced)
- Practical scenarios
- Starter code provided
- Hints included
- Complete solutions with explanations

### ✅ **Explanations:**
- Beginner-friendly language
- Hinglish where natural
- Markdown formatting
- Real-world use cases
- Indian student perspective

### ✅ **Resources:**
- Official documentation
- Trusted articles/tutorials
- Video resources
- Development tools

---

## 🐛 Troubleshooting

### **Problem 1: "API key not configured"**

**Solution:**
```bash
# Check .env file
cat .env | grep GEMINI_API_KEY

# If missing, add it:
echo "GEMINI_API_KEY=your-key-here" >> .env
```

---

### **Problem 2: "Failed to parse AI response"**

**Causes:**
- Malformed JSON from API
- API rate limit exceeded
- Network timeout

**Solution:**
```php
// Enable debug mode in AIContentGenerator.php
private function callGemini($prompt) {
    // Add after API call
    error_log("API Response: " . $response);
    
    // Check for rate limit
    $result = json_decode($response, true);
    if (isset($result['error'])) {
        throw new Exception("API Error: " . $result['error']['message']);
    }
}
```

---

### **Problem 3: Content generation hangs/times out**

**Solution:**
```php
// Increase timeout in AIContentGenerator.php
curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 2 minutes

// Or use async processing (recommended for bulk)
// Implement queue system with background workers
```

---

### **Problem 4: Poor quality content**

**Solution:**
```php
// Modify prompt in buildPrompt() method
// Add more specific instructions:
$prompt .= "\n\n**ADDITIONAL REQUIREMENTS:**";
$prompt .= "\n- Use real Indian names (Rahul, Priya, Amit)";
$prompt .= "\n- Include Mumbai/Delhi/Bangalore examples";
$prompt .= "\n- Explain in simple Hindi for rural students";
$prompt .= "\n- Add cricket/Bollywood analogies where relevant";
```

---

### **Problem 5: Database errors**

**Solution:**
```sql
-- Check if table exists
SHOW TABLES LIKE 'lesson_content';

-- If missing, create it:
SOURCE database/lesson_content.sql;

-- Check foreign key constraints
SHOW CREATE TABLE lesson_content;
```

---

## 🔄 Content Update Workflow

```
┌──────────────────┐
│  Create Course   │
│    & Lessons     │
└────────┬─────────┘
         │
         ↓
┌──────────────────┐
│  Generate        │
│  Content (Draft) │
└────────┬─────────┘
         │
         ↓
┌──────────────────┐
│  Review Content  │
│  (Admin checks)  │
└────────┬─────────┘
         │
    ┌────┴────┐
    │         │
   Good     Needs Work
    │         │
    ↓         ↓
┌────────┐ ┌──────────────┐
│Publish │ │ Regenerate   │
│        │ │ or Edit      │
└────────┘ └──────┬───────┘
              │
              └──────→ (back to Review)
```

---

## 📈 Performance Optimization

### **1. Batch Processing:**

```php
// Generate multiple lessons asynchronously
foreach ($lessonIds as $lessonId) {
    // Queue the job
    $queue->push(new GenerateContentJob($lessonId));
}

// Process in background worker
// worker.php:
while ($job = $queue->pop()) {
    $job->handle();
}
```

### **2. Caching:**

```php
// Cache AI responses to avoid regeneration
$cacheKey = "lesson_content_{$lessonId}_{$language}";
$content = $cache->get($cacheKey);

if (!$content) {
    $content = $generator->generateLessonContent($lessonId, $language);
    $cache->set($cacheKey, $content, 3600); // 1 hour
}
```

### **3. Rate Limiting:**

```php
// Avoid API rate limits
class RateLimiter {
    private $requests = [];
    private $limit = 10; // requests per minute
    
    public function throttle() {
        // Remove requests older than 1 minute
        $this->requests = array_filter($this->requests, fn($t) => time() - $t < 60);
        
        if (count($this->requests) >= $this->limit) {
            sleep(60 - (time() - $this->requests[0]));
        }
        
        $this->requests[] = time();
    }
}
```

---

## 🎓 Best Practices

### ✅ **DO:**
- Generate content in batches during off-hours
- Review AI-generated content before publishing
- Use `hi+en` for maximum accessibility
- Add custom examples for your specific course
- Keep API keys secure (never commit to git)
- Monitor API usage and costs
- Cache generated content

### ❌ **DON'T:**
- Don't publish without review
- Don't expose API keys in frontend
- Don't regenerate unnecessarily (wastes API calls)
- Don't trust AI blindly (verify code examples)
- Don't generate during peak hours (slow for users)

---

## 🌟 Advanced Features

### **1. Custom Prompts per Course:**

```php
// In buildPrompt(), add course-specific context
if ($lesson['course_id'] == 1) {
    $prompt .= "\n\nThis is for absolute beginners in rural India.";
    $prompt .= "\nUse very simple language and agricultural examples.";
} elseif ($lesson['course_id'] == 5) {
    $prompt .= "\n\nThis is an advanced course for professionals.";
    $prompt .= "\nInclude complex algorithms and system design.";
}
```

### **2. Multi-Model Support:**

```php
// Use different models for different content types
if ($lesson['type'] == 'coding') {
    $this->model = 'gemini-1.5-pro';  // Better for code
} else {
    $this->model = 'gemini-1.5-flash'; // Faster for theory
}
```

### **3. Content Templates:**

```php
// Predefined templates for common lesson types
$templates = [
    'introduction' => 'Basic introduction with minimal code',
    'tutorial' => 'Step-by-step guide with examples',
    'practice' => 'Focus on exercises and challenges',
    'project' => 'Real-world project with complete code'
];
```

---

## 📞 Support & Help

**Documentation:**
- This guide: `LESSON_CONTENT_GENERATION_GUIDE.md`
- API docs: `API_DOCUMENTATION.md`

**Common Issues:**
- API errors → Check `.env` configuration
- Slow generation → Use batch processing
- Poor quality → Customize prompts

**Contact:**
- Create issue on GitHub
- Email: support@tcm.com

---

## 🎉 Summary

Is system se tum:
1. ✅ One-click content generation
2. ✅ Bilingual support (Hindi + English)
3. ✅ High-quality code examples
4. ✅ Practice exercises automatically
5. ✅ FREE (using Gemini API)

**Next Steps:**
1. Verify `.env` has `GEMINI_API_KEY`
2. Open admin panel
3. Create a course & lessons
4. Click "Generate Content"
5. Review & publish! 🚀

Happy Teaching! 📚✨
