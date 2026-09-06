# 🤖 AI Content Generator - Complete Guide

## 📌 Overview

**AI Content Generator** एक powerful system है जो automatically **detailed lesson content** generate करता है हर course lesson के लिए। अब बस lesson का title add करो, बाकी सब AI generate कर देगा!

### ✨ Features

1. ✅ **Automatic Content Generation** - OpenAI GPT-4o का use करके
2. ✅ **Real Working Code Examples** - न सिर्फ comments, actual tested code
3. ✅ **Progressive Exercises** - Beginner से Advanced level तक
4. ✅ **Bilingual Support** - Hindi + English दोनों में content
5. ✅ **Interactive Practice** - Students exercises submit कर सकते हैं
6. ✅ **Admin Review System** - Publish करने से पहले review करो
7. ✅ **CLI Tool** - Terminal से batch generation के लिए

---

## 🚀 Quick Start

### 1️⃣ Database Setup

```bash
# Database tables create करो
mysql -u root -p tcm < database/lesson_content.sql
```

### 2️⃣ OpenAI API Key Setup

`.env` file में add करो:

```env
OPENAI_API_KEY=sk-proj-xxxxxxxxxxxxx
```

**API Key kaise milega?**
1. Visit: https://platform.openai.com/api-keys
2. "Create new secret key" click करो
3. Key copy करके `.env` में paste करो
4. Billing setup करो: https://platform.openai.com/settings/organization/billing

**💰 Cost:** ~$0.02-0.05 per lesson (GPT-4o pricing)

### 3️⃣ Test करो

```bash
# Status check करो
php generate-content.php --status

# Single lesson के लिए generate करो
php generate-content.php --lesson=4

# Puri module के liye
php generate-content.php --module=3
```

---

## 📚 Usage Guide

### Admin Panel से Use करना

1. **Admin Dashboard** में login करो
2. Navigate to: `/admin/ai-content`
3. **Course select करो** जिसके लिए content generate करना है
4. Options:
   - 🔵 **Generate Content** - Single lesson के लिए
   - 🟢 **Generate All Missing** - सारे missing lessons के लिए
   - 👁️ **View Content** - Preview देखो
   - 🔄 **Regenerate** - Content फिर से generate करो
   - ✅ **Publish** - Content live करो

### CLI से Use करना

```bash
# Help
php generate-content.php --help

# Status देखो - कौन से lessons में content है/नहीं
php generate-content.php --status

# Single lesson generate करो
php generate-content.php --lesson=4

# Module की सारी lessons generate करो
php generate-content.php --module=3

# Course की सारी lessons
php generate-content.php --course=1

# सारे missing lessons generate करो
php generate-content.php --all

# Regenerate existing content
php generate-content.php --regenerate --lesson=4

# Language specify करो
php generate-content.php --lesson=4 --lang=hi+en
```

**Language Options:**
- `hi+en` - Hindi + English (default, best for Indian students)
- `hi` - Hindi only
- `en` - English only

---

## 🎯 Generated Content Structure

हर lesson के लिए AI यह generate करता है:

### 1. **Overview** (2-3 sentences)
```
Brief introduction जो lesson cover करेगा
```

### 2. **Key Concepts** (5-8 concepts)
```json
{
  "title": "Array Methods",
  "explanation": "Detailed explanation in Hindi/English",
  "importance": "Why this matters"
}
```

### 3. **Detailed Explanation**
- Markdown formatted
- Step-by-step breakdown
- Real-world analogies
- Visual diagrams (where applicable)

### 4. **Code Examples** (3-5 examples)
```javascript
// Real, working code
const numbers = [1, 2, 3, 4, 5];
const doubled = numbers.map(num => num * 2);
console.log(doubled); // [2, 4, 6, 8, 10]
```

हर example में:
- ✅ **Complete working code**
- ✅ **Expected output**
- ✅ **Line-by-line explanation**
- ✅ **Common mistakes to avoid**

### 5. **Practice Exercises** (3-5 exercises)

**Beginner:**
```javascript
// Exercise: Create an array filter function
const ages = [12, 16, 18, 21, 25];
// TODO: Filter ages >= 18
```

**Intermediate:**
```javascript
// Exercise: Implement custom map function
// Your implementation here
```

**Advanced:**
```javascript
// Exercise: Build a shopping cart with array methods
```

हर exercise में:
- Starter code
- Hints
- Complete solution
- Test cases

### 6. **Resources**
- MDN Documentation links
- Video tutorials
- Interactive tools
- Articles

---

## 👨‍🎓 Student Experience

### Content देखना

Students enrolled courses में content access कर सकते हैं:

```
GET /student/lesson-content/{lessonId}?lang=hi
```

Response:
```json
{
  "overview": "Arrays are...",
  "key_concepts": [...],
  "explanation": "...",
  "code_examples": [...],
  "exercises": [...],
  "resources": [...]
}
```

### Exercises Submit करना

```javascript
// JavaScript से
fetch('/student/lesson-content/submit-exercise', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    lesson_id: 4,
    exercise_index: 0,
    code: '// student solution'
  })
});
```

### Progress Tracking

Students apne submissions track कर सकते हैं:
```
GET /student/lesson-content/my-submissions/{lessonId}
```

---

## 🔧 API Reference

### Admin Endpoints

#### Get Content Status
```http
GET /admin/ai-content/status?course_id=1
```

Response:
```json
{
  "success": true,
  "lessons": {
    "Course Name": {
      "Module Name": [
        {
          "lesson_id": 4,
          "title": "Arrays & Array Methods",
          "has_content": true,
          "status": "published",
          "language": "hi+en"
        }
      ]
    }
  }
}
```

#### Generate Content
```http
POST /admin/ai-content/generate-lesson
Content-Type: application/json

{
  "lesson_id": 4,
  "language": "hi+en"
}
```

#### Generate Module
```http
POST /admin/ai-content/generate-module
Content-Type: application/json

{
  "module_id": 3,
  "language": "hi+en"
}
```

#### Publish Content
```http
POST /admin/ai-content/publish
Content-Type: application/json

{
  "lesson_id": 4
}
```

### Student Endpoints

#### Get Lesson Content
```http
GET /student/lesson-content/{lessonId}?lang=hi
```

Requires:
- Student must be enrolled in the course
- Content must be published

#### Submit Exercise
```http
POST /student/lesson-content/submit-exercise
Content-Type: application/json

{
  "lesson_id": 4,
  "exercise_index": 0,
  "code": "// solution code"
}
```

---

## 📊 Database Schema

### `lesson_content`
Stores the generated content:

```sql
- id
- lesson_id (FK to course_lessons)
- overview_hi, overview_en
- key_concepts (JSON)
- explanation_hi, explanation_en
- code_examples (JSON)
- exercises (JSON)
- resources (JSON)
- status (draft/reviewed/published)
- ai_generated, ai_model
- generated_at
```

### `exercise_submissions`
Tracks student exercise submissions:

```sql
- id
- user_id (FK to users)
- lesson_id (FK to course_lessons)
- exercise_index
- code
- status (pending/correct/incorrect/review)
- feedback
- attempts
```

---

## 🎨 Customization

### AI Prompt Customize करना

`src/Services/AIContentGenerator.php` में edit करो:

```php
private function buildPrompt(array $lesson, string $language): string
{
    // Apni custom instructions add karo
    $customInstructions = "
        - Use simple, relatable examples
        - Focus on practical applications
        - Include common mistakes section
    ";
    
    // ... rest of prompt
}
```

### Different AI Model Use करना

```php
private string $model = 'gpt-4o'; // Change to gpt-4, gpt-3.5-turbo, etc.
```

### API Provider Change करना

OpenAI की जगह दूसरा provider use करना चाहते हो?

**Gemini (Google):**
```php
private string $apiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
```

**Claude (Anthropic):**
```php
private string $apiEndpoint = 'https://api.anthropic.com/v1/messages';
```

---

## 🚨 Troubleshooting

### "OpenAI API key not configured"
**Fix:** `.env` में `OPENAI_API_KEY` add करो

### "Content not found"
**Reasons:**
1. Content abhi generate nahi hua
2. Content status "draft" hai, "published" nahi
3. Student enrolled nahi hai

**Fix:**
```bash
# Generate करो
php generate-content.php --lesson=4

# Publish करो (Admin panel se)
```

### "API request failed: timeout"
**Reasons:**
- Network slow hai
- OpenAI API down hai
- Rate limit exceed ho gaya

**Fix:**
```php
// Timeout badhao
curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 120 seconds
```

### Content Quality Low Hai

**Solutions:**
1. Model upgrade करो: `gpt-3.5-turbo` → `gpt-4o`
2. Prompt improve करो (more specific instructions)
3. Examples add करो prompt में
4. Temperature adjust करो (0.7 → 0.5 for more focused output)

### Too Expensive

**Cost Reduction:**
1. Use `gpt-3.5-turbo` instead of `gpt-4o` ($0.001 vs $0.005 per 1K tokens)
2. Reduce `max_tokens` (4000 → 2000)
3. Generate only for important lessons
4. Batch process during off-peak hours

---

## 💡 Best Practices

### 1. **Always Review Before Publishing**
AI-generated content को blindly publish mat karo. Admin panel se review karo:
- Code examples test karo
- Exercises solve karke dekho
- Language/grammar check karo

### 2. **Start with One Module**
Pehle ek module generate karo aur quality dekho, phir scale karo.

### 3. **Use Batch Processing**
CLI tool use karke multiple lessons generate karo:
```bash
php generate-content.php --course=1 --lang=hi+en
```

### 4. **Version Control**
Content versions maintain karo. Regenerate karne se pehle backup lo.

### 5. **Student Feedback**
Students se feedback lo aur content improve karo.

### 6. **Monitor Costs**
OpenAI dashboard pe usage track karo:
https://platform.openai.com/usage

---

## 🔮 Future Enhancements

### Planned Features

1. **AI Code Evaluation**
   - Students ke exercise submissions automatically evaluate karo
   - Instant feedback do

2. **Adaptive Difficulty**
   - Student ke level ke according exercises generate karo

3. **Video Integration**
   - Code examples ke liye auto-generated video tutorials

4. **Quiz Generation**
   - MCQs automatically generate karo

5. **Multi-language Support**
   - More languages add karo (Tamil, Telugu, etc.)

6. **Offline Mode**
   - Pre-generated content cache karke offline access

---

## 📞 Support

**Issues?** 
- Check logs: `storage/logs/`
- Test API key: `php debug-env.php`
- Contact: thecodemunk@gmail.com

**Resources:**
- OpenAI API Docs: https://platform.openai.com/docs
- PHP cURL Guide: https://www.php.net/manual/en/book.curl.php

---

## 📝 License

Part of The Code Munk (TCM) Ed-Tech Platform.
For internal use only.

---

**Made with ❤️ for Indian students**

*Happy Teaching! 🚀*
