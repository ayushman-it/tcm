# 📱 Tutorial Page - Visual Preview

## What the Tutorial Page Looks Like

```
┌─────────────────────────────────────────────────────────────────────┐
│  TCM Dashboard Header (Logo, Navigation, Profile)                   │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────┬──────────────────────────────────────────────────┐
│                  │                                                  │
│  📚 CONTENTS     │  Breadcrumb: My Tasks / Build a Calculator       │
│  ══════════════  │                                                  │
│                  │  # Build a Calculator using JavaScript           │
│  Introduction    │  [Easy] [~45 min] [JavaScript Course]           │
│  Prerequisites   │  ─────────────────────────────────────────────   │
│  Step-by-Step    │                                                  │
│  Complete Code   │  💡 INTRODUCTION                                 │
│  Expected Output │  ┌────────────────────────────────────────────┐ │
│  Try It Yourself │  │ In this tutorial, you'll learn to build a  │ │
│  Common Mistakes │  │ fully functional calculator using          │ │
│  Summary         │  │ JavaScript functions, operators, and DOM.  │ │
│  (Active: blue)  │  └────────────────────────────────────────────┘ │
│                  │                                                  │
│  [Sticky on      │  📋 PREREQUISITES                                │
│   desktop,       │  ✓ Basic understanding of JavaScript            │
│   collapsed      │  ✓ Knowledge of HTML and CSS                    │
│   on mobile]     │  ✓ Text editor or IDE installed                 │
│                  │                                                  │
│                  │  📝 STEP-BY-STEP GUIDE                           │
│                  │  ┌────────────────────────────────────────────┐ │
│                  │  │ Step 1: Create the HTML Structure          │ │
│                  │  │ First, we'll create the basic HTML layout  │ │
│                  │  │ for our calculator interface.              │ │
│                  │  │                                            │ │
│                  │  │ ┌──────────────────────────────────┐ html │ │
│                  │  │ │ <div class="calculator">         │      │ │
│                  │  │ │   <input id="display" readonly>  │      │ │
│                  │  │ │   <div class="buttons">          │      │ │
│                  │  │ │     <button>7</button>           │      │ │
│                  │  │ │     <button>8</button>           │      │ │
│                  │  │ │   </div>                         │      │ │
│                  │  │ │ </div>                           │      │ │
│                  │  │ └──────────────────────────────────┘      │ │
│                  │  └────────────────────────────────────────────┘ │
│                  │                                                  │
│                  │  ┌────────────────────────────────────────────┐ │
│                  │  │ Step 2: Add CSS Styling                    │ │
│                  │  │ Style the calculator to make it look good  │ │
│                  │  │ ...                                        │ │
│                  │  └────────────────────────────────────────────┘ │
│                  │                                                  │
│                  │  [More steps...]                                 │
│                  │                                                  │
│                  │  💻 COMPLETE CODE EXAMPLE                        │
│                  │  Here's the complete working code:               │
│                  │  ┌──────────────────────────────────┐ javascript│
│                  │  │ function calculate(operation) {  │           │
│                  │  │   const display =                │           │
│                  │  │     document.getElementById(...) │           │
│                  │  │   // Full implementation         │           │
│                  │  │ }                                │           │
│                  │  └──────────────────────────────────┘           │
│                  │                                                  │
│                  │  ✨ EXPECTED OUTPUT                               │
│                  │  ┌────────────────────────────────────────────┐ │
│                  │  │ When you run this code, you should see:    │ │
│                  │  │ A working calculator that can add,         │ │
│                  │  │ subtract, multiply, and divide numbers.    │ │
│                  │  └────────────────────────────────────────────┘ │
│                  │                                                  │
│                  │  ╔════════════════════════════════════════════╗ │
│                  │  ║ 🚀 TRY IT YOURSELF!                        ║ │
│                  │  ║ Now it's your turn! Try these exercises:  ║ │
│                  │  ╚════════════════════════════════════════════╝ │
│                  │  ✓ Add a clear button to reset calculator       │
│                  │  ✓ Implement percentage calculations            │
│                  │  ✓ Add keyboard support for number entry        │
│                  │                                                  │
│                  │  ⚠️ COMMON MISTAKES                              │
│                  │  ┌────────────────────────────────────────────┐ │
│                  │  │ Watch out for these common errors:        │ │
│                  │  └────────────────────────────────────────────┘ │
│                  │  ! Not validating user input                    │
│                  │  ! Forgetting to handle division by zero        │
│                  │  ! Not updating display after operations        │
│                  │                                                  │
│                  │  🎯 SUMMARY                                      │
│                  │  ┌────────────────────────────────────────────┐ │
│                  │  │ You've learned how to build a calculator   │ │
│                  │  │ using JavaScript! Key concepts:            │ │
│                  │  │ - Event handling, DOM manipulation,        │ │
│                  │  │   functions, and operators.                │ │
│                  │  └────────────────────────────────────────────┘ │
│                  │                                                  │
│                  │  ─────────────────────────────────────────────   │
│                  │  [✓ Mark as Completed] [📚 Go to Course] [Back] │
│                  │                                                  │
└──────────────────┴──────────────────────────────────────────────────┘
```

---

## 🎨 Color Scheme

### Sidebar:
- Background: White (`#fff`)
- Border: Light gray (`#e5e7eb`)
- Active link: Purple (`#4f46e5`)
- Hover: Light gray background (`#f3f4f6`)

### Main Content:
- Background: White (`#fff`)
- Text: Dark gray (`#374151`)
- Headings: Black (`#111827`)
- Border: Light gray (`#e5e7eb`)

### Info Boxes:
- **Introduction**: Blue background (`#eff6ff`)
- **Success**: Green background (`#f0fdf4`)
- **Warning**: Yellow background (`#fefce8`)
- **Error**: Red background (`#fef2f2`)

### Code Blocks:
- Background: Dark slate (`#1e293b`)
- Text: Light gray (`#e2e8f0`)
- Language badge: Gray (`#94a3b8`)

### Badges:
- **Easy**: Green (`#dcfce7` / `#166534`)
- **Medium**: Yellow (`#fef3c7` / `#92400e`)
- **Hard**: Red (`#fee2e2` / `#991b1b`)
- **Time**: Blue (`#dbeafe` / `#1e40af`)
- **Course**: Purple (`#f3e8ff` / `#6b21a8`)

### Buttons:
- **Primary**: Purple (`#4f46e5`)
- **Success**: Green (`#22c55e`)
- **Secondary**: White with gray border

---

## 📱 Mobile View

```
┌────────────────────────────┐
│  TCM Header                │
├────────────────────────────┤
│  ☰ Contents (Collapsible)  │
├────────────────────────────┤
│                            │
│  Build a Calculator        │
│  [Easy] [45 min]           │
│  ──────────────────────    │
│                            │
│  💡 INTRODUCTION           │
│  [Box with text]           │
│                            │
│  📋 PREREQUISITES          │
│  ✓ Item 1                  │
│  ✓ Item 2                  │
│                            │
│  📝 STEP 1                 │
│  [Explanation]             │
│  [Code block]              │
│                            │
│  📝 STEP 2                 │
│  [Explanation]             │
│                            │
│  [Scroll down...]          │
│                            │
│  ──────────────────────    │
│  [Mark Completed]          │
│  [Go to Course]            │
│  [Back to Tasks]           │
│                            │
└────────────────────────────┘
```

---

## 🔄 Interactive Elements

### 1. Sidebar Navigation
```javascript
Click any section → Smooth scroll to that section
On scroll → Auto-highlight current section
Mobile → Collapsible menu
```

### 2. Code Blocks
- Dark theme for better readability
- Language badge in top-right corner
- Horizontal scroll for long lines
- Copy button (can be added later)

### 3. Action Buttons
```javascript
"Mark as Completed" → POST to /student/tasks/{id}/status
                   → Toast notification: "✅ Great job!"
                   → Redirect to task list

"Go to Course"     → Navigate to /student/learn/{course_id}

"Back to Tasks"    → Navigate to /student/tasks
```

### 4. Animations
- Smooth scroll (500ms ease-out)
- Button hover effects (transform + shadow)
- Toast notifications (slide in from right)
- Section highlighting (color transition)

---

## 💡 Real Example

### Sample Task: "Create a Responsive Card Component"

**URL**: `/student/tasks/123`

**Tutorial Sections Generated by AI:**

1. **Introduction**
   > "Learn to create a modern, responsive card component using HTML, CSS Flexbox, and hover effects. Perfect for displaying content like blog posts, products, or user profiles."

2. **Prerequisites**
   - Basic HTML knowledge
   - Understanding of CSS Flexbox
   - Text editor installed

3. **Step-by-Step Guide**
   - Step 1: HTML Structure → Card container, image, title, description
   - Step 2: Basic CSS Styling → Colors, fonts, spacing
   - Step 3: Flexbox Layout → Centering, alignment
   - Step 4: Responsive Design → Media queries
   - Step 5: Hover Effects → Transitions, transforms, shadows

4. **Complete Example**
   ```html
   <div class="card">
     <img src="..." alt="Card image">
     <div class="card-content">
       <h3>Card Title</h3>
       <p>Card description text here...</p>
       <button>Read More</button>
     </div>
   </div>
   ```

5. **Expected Output**
   > "A beautiful card that looks great on all screen sizes, with smooth hover animations and responsive behavior."

6. **Try It Yourself**
   - Add a card footer with social share buttons
   - Create a grid of multiple cards
   - Add a flip animation on hover

7. **Common Mistakes**
   - Not setting box-sizing: border-box
   - Forgetting max-width for large screens
   - Hard-coding sizes instead of using relative units

8. **Summary**
   > "You've mastered responsive card components! These are used everywhere in modern web design. Practice by creating different card variations."

---

## 📊 Analytics (Future)

Can track:
- Which sections students spend most time on
- Completion rates per tutorial
- Most helpful sections (feedback)
- Common drop-off points
- AI tutorial quality ratings

---

## 🎯 Success Metrics

### What to Measure:
1. **Task completion rate** (before vs after tutorials)
2. **Time spent on tutorials** (engagement)
3. **"Mark as Completed" clicks** (success rate)
4. **Return to course** clicks (follow-up learning)
5. **Tutorial views per task** (popularity)

### Expected Improvements:
- 📈 +30% task completion rate
- 📈 +50% student engagement
- 📉 -40% support queries
- 📈 +20% course completion rate

---

This is the complete visual and functional preview of your new **Task Tutorial System**! 🎉
