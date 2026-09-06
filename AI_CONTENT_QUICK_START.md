# 🚀 AI Content Generator - Quick Start

## 5-Minute Setup Guide

### Step 1: Database (1 min)
```bash
php setup-ai-content.php
```

### Step 2: API Key (2 min)
1. Get key: https://platform.openai.com/api-keys
2. Add to `.env`:
```env
OPENAI_API_KEY=sk-proj-xxxxx
```
3. Setup billing: https://platform.openai.com/settings/organization/billing

### Step 3: Generate (2 min)
```bash
# Test single lesson
php generate-content.php --lesson=4

# Or use admin panel
http://localhost:8000/admin/ai-content
```

---

## 📝 What Gets Generated?

For lesson: **"Arrays & Array Methods"**

✅ **Overview** (2-3 sentences)  
✅ **5-8 Key Concepts** with explanations  
✅ **3-5 Real Code Examples** (tested & working)  
✅ **3-5 Progressive Exercises** (beginner → advanced)  
✅ **Resources** (docs, videos, tools)  
✅ **Bilingual** (Hindi + English)

**Time:** 30-60 seconds per lesson  
**Cost:** ₹2-4 per lesson (GPT-4o)

---

## 🎯 Quick Commands

```bash
# Check status
php generate-content.php --status

# Generate single lesson
php generate-content.php --lesson=4

# Generate whole module
php generate-content.php --module=3

# Generate all missing
php generate-content.php --all

# Regenerate existing
php generate-content.php --regenerate --lesson=4
```

---

## 🌐 Admin Panel

**URL:** `/admin/ai-content`

Features:
- 👀 View all lessons with content status
- ⚡ Generate single/batch
- 📝 Preview before publish
- 🔄 Regenerate if needed
- ✅ Publish to students

---

## 👨‍🎓 Student Access

Students can:
- View detailed lesson content
- Run code examples
- Submit exercises
- Track progress

**API:** `GET /student/lesson-content/{id}`

---

## 💡 Tips

1. **Start Small** - Test with 1-2 lessons first
2. **Review Before Publishing** - Always check quality
3. **Monitor Costs** - Check OpenAI dashboard
4. **Use Batch** - Generate module/course at once
5. **Get Feedback** - Ask students for improvements

---

## 🚨 Troubleshooting

| Problem | Solution |
|---------|----------|
| API key not found | Add to `.env` file |
| Insufficient quota | Setup billing + add credits |
| Slow generation | Normal (30-60s), use batch for multiple |
| Content quality low | Use GPT-4o, improve prompts |
| Too expensive | Use GPT-3.5-turbo (cheaper) |

---

## 📚 Full Docs

- **English:** `AI_CONTENT_GENERATOR_GUIDE.md`
- **Hindi:** `AI_CONTENT_GENERATOR_HINDI.md`

---

## ✨ Example Output

```javascript
// Generated Code Example
const fruits = ['apple', 'banana', 'orange'];

// map - transform each element
const upperFruits = fruits.map(fruit => fruit.toUpperCase());
console.log(upperFruits); // ['APPLE', 'BANANA', 'ORANGE']

// filter - select matching elements
const longNames = fruits.filter(fruit => fruit.length > 5);
console.log(longNames); // ['banana', 'orange']
```

**+ Detailed explanation in Hindi & English**  
**+ Progressive exercises with solutions**  
**+ Resources & best practices**

---

**That's it! Now go generate some amazing content! 🎉**

Questions? Read full docs or contact: thecodemunk@gmail.com
