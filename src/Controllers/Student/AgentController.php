<?php
declare(strict_types=1);
namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Request;
use TCM\Core\Response;

final class AgentController extends Controller
{
    public function index(): void
    {
        $user = Auth::require('student');

        $this->view('student/agent/index', [
            'title' => 'TCM Agent',
            'user'  => $user,
        ], 'student');
    }

    public function chat(): void
    {
        $user = Auth::require('student');
        
        $message = trim(Request::string('message'));
        
        if (empty($message)) {
            Response::error('Please enter a message', 400);
            return;
        }

        try {
            // Use OpenRouter AI
            require_once __DIR__ . '/../../Services/OpenRouterAI.php';
            $ai = new \TCM\Services\OpenRouterAI();
            
            // Build COMPREHENSIVE context for teaching
            $context = $this->buildTeachingContext($user, $message);
            
            // Get AI response with higher token limit for teaching
            $maxTokens = $this->needsDetailedResponse($message) ? 2000 : 1000;
            
            $response = $ai->chat($message, $context, [
                'maxTokens' => $maxTokens,
                'temperature' => 0.8,
                'model' => 'openai/gpt-4o-mini'
            ]);
            
            Response::success([
                'message' => $response,
                'timestamp' => date('c'),
                'powered_by' => 'OpenRouter AI'
            ]);
            
        } catch (\Exception $e) {
            error_log("TCM Agent Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Improved fallback response
            $response = $this->getSmartFallback($message, $user);
            
            Response::success([
                'message' => $response,
                'timestamp' => date('c'),
                'powered_by' => 'Fallback System'
            ]);
        }
    }

    /**
     * Check if message needs detailed teaching response
     */
    private function needsDetailedResponse(string $message): bool
    {
        $lower = strtolower($message);
        $teachKeywords = ['teach', 'explain', 'how to', 'what is', 'kaise', 'kya hai', 'code', 'example', 'class', 'function'];
        
        foreach ($teachKeywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Build comprehensive teaching context
     */
    private function buildTeachingContext(array $user, string $message): string
    {
        $isTeachingQuery = $this->needsDetailedResponse($message);
        
        if ($isTeachingQuery) {
            // DETAILED teaching context
            return <<<CONTEXT
You are TCM Agent - an expert programming tutor for Indian students learning to code.

Student: {$user['name']} ({$user['email']})

🎯 YOUR PRIMARY ROLE: TEACH PROGRAMMING

When student asks "teach me" or "how to" or "explain":
✅ Provide COMPLETE working code (minimum 20-25 lines)
✅ Explain step-by-step in simple Hinglish
✅ Show real examples with OUTPUT
✅ Break down complex concepts into simple parts
✅ Use analogies that Indian students understand

EXAMPLE OF GOOD RESPONSE:
User: "teach me how to write a class"

You: "Chalo class ke baare mein seekhte hain! 🚀

# Class Kya Hai?
Class ek blueprint hai jisse hum objects banate hain. Jaise ghar ka blueprint hota hai, waisa hi code mein class hoti hai.

# Real Example - Student Class

\`\`\`javascript
// Step 1: Class define karo
class Student {{
    // Constructor - jab new student bane tab ye run hoga
    constructor(name, age, course) {{
        this.name = name;
        this.age = age;
        this.course = course;
        this.marks = [];
    }}
    
    // Method 1: Student ko introduce karna
    introduce() {{
        return `Namaste! Main ${{this.name}} hoon. Main ${{this.course}} course kar raha hoon.`;
    }}
    
    // Method 2: Marks add karna
    addMarks(subject, score) {{
        this.marks.push({{ subject, score }});
        console.log(`${{subject}} mein ${{score}} marks add ho gaye!`);
    }}
    
    // Method 3: Average marks nikalna
    getAverage() {{
        if (this.marks.length === 0) return 0;
        const total = this.marks.reduce((sum, m) => sum + m.score, 0);
        return total / this.marks.length;
    }}
    
    // Method 4: Display all info
    displayInfo() {{
        console.log('=== Student Details ===');
        console.log('Name:', this.name);
        console.log('Age:', this.age);
        console.log('Course:', this.course);
        console.log('Average Marks:', this.getAverage());
    }}
}}

// Step 2: Class use karo (Object banana)
const raj = new Student('Raj Kumar', 20, 'Web Development');
const priya = new Student('Priya Sharma', 19, 'Data Science');

// Step 3: Methods call karo
console.log(raj.introduce());
// Output: "Namaste! Main Raj Kumar hoon. Main Web Development course kar raha hoon."

raj.addMarks('JavaScript', 85);
raj.addMarks('React', 90);
raj.addMarks('Node.js', 88);

priya.addMarks('Python', 92);
priya.addMarks('ML', 87);

// Step 4: Information dekho
raj.displayInfo();
// Output:
// === Student Details ===
// Name: Raj Kumar
// Age: 20
// Course: Web Development
// Average Marks: 87.67

priya.displayInfo();
\`\`\`

# Output Kya Aayega:
\`\`\`
Namaste! Main Raj Kumar hoon. Main Web Development course kar raha hoon.
JavaScript mein 85 marks add ho gaye!
React mein 90 marks add ho gaye!
Node.js mein 88 marks add ho gaye!
Python mein 92 marks add ho gaye!
ML mein 87 marks add ho gaye!
=== Student Details ===
Name: Raj Kumar
Age: 20
Course: Web Development
Average Marks: 87.67
=== Student Details ===
Name: Priya Sharma
Age: 19
Course: Data Science
Average Marks: 89.5
\`\`\`

# Key Points:
1. **constructor()** - Object banate time run hota hai
2. **this.property** - Object ki apni property
3. **methods** - Functions jo class ke andar hote hain
4. **new keyword** - Class se object banana

Samajh aaya? Koi doubt ho to poochho! 💡"

❌ NEVER GIVE SHORT ANSWERS like:
- "You can use class keyword to create a class"
- "Refer to documentation"
- "Check course materials"

🌟 ALWAYS BE:
- Detailed and thorough
- Use Hinglish for clarity
- Provide complete runnable code
- Show actual output
- Friendly and encouraging

Aapka kaam hai students ko ACTUALLY programming sikhana, sirf information dena nahi! 🎓
CONTEXT;
        } else {
            // Platform help context
            return <<<CONTEXT
You are TCM Agent - a friendly assistant for The Code Munk platform.

Student: {$user['name']}

Help with:
📚 Courses, 💰 Payments, 💵 Wallet, 📋 Tasks, 👥 Community, 💼 Portfolio, 🗓 Events

Be concise, helpful, and friendly. Use Hinglish when appropriate.
CONTEXT;
        }
    }

    /**
     * Smart fallback when AI fails
     */
    private function getSmartFallback(string $message, array $user): string
    {
        $lower = strtolower($message);
        
        // Teaching queries - give actual help
        if ($this->needsDetailedResponse($lower)) {
            return "Maaf kijiye! 🙏\n\nAI service temporarily unavailable hai. Par main aapki madad kar sakta hoon!\n\n📚 **Aap ye try kar sakte hain:**\n\n1. **Dashboard** mein jaake **Courses** section mein detailed lessons dekho\n2. **Daily Tasks** mein practice problems solve karo\n3. **Community** mein fellow students se help lo\n\nKya aap specific topic ke baare mein puchna chahte hain? Thodi der baad dobara try karo! 💪";
        }
        
        // Standard fallback
        return $this->generateResponse($message, $user);
    }

    /**
     * Build AI context for student
     */
    private function buildAIContext(array $user): string
    {
        return <<<CONTEXT
You are TCM Agent, a helpful AI tutor for The Code Munk - an Indian ed-tech platform.

**Student:** {$user['name']} ({$user['email']})

**Your Capabilities:**
1. 💻 **Programming Teacher**
   - Explain ANY programming concept
   - Write COMPLETE working code
   - Debug code errors
   - Teach step-by-step
   - Use Hinglish for Indian students

2. 📚 **Platform Guide**
   - Course recommendations
   - Payment help
   - Wallet queries
   - Tasks and progress
   - Events and community

**Important Rules:**
✅ For coding questions: Provide COMPLETE working code (minimum 10-15 lines)
✅ For "teach me" questions: Give detailed step-by-step explanations
✅ For "how to" questions: Provide examples and code
✅ Use simple language, Hinglish is okay
✅ Be friendly and encouraging
✅ Use emojis appropriately

❌ NEVER say "refer to course materials"
❌ NEVER give incomplete code
❌ NEVER use placeholder comments like "// code here"

Examples of GOOD responses:
- "Here's how to write a class in JavaScript: [complete code with explanation]"
- "Let me show you with a real example: [working code]"
- "Here's step-by-step: 1. First... 2. Then... [with code]"

Be a real programming tutor, not just an information bot!
CONTEXT;
    }

    private function generateResponse(string $message, array $user): string
    {
        // Enhanced rule-based responses with more context
        $lower = strtolower($message);
        $name = $user['name'];
        
        // Course related
        if (str_contains($lower, 'course') || str_contains($lower, 'learn')) {
            return "Hi {$name}! 👋\n\n📚 **Course Help**\n\nI can help you with:\n• Browse available courses\n• View your enrolled courses\n• Track learning progress\n• Get course recommendations\n\nWhat specific course are you interested in?";
        }
        
        // Payment related
        if (str_contains($lower, 'payment') || str_contains($lower, 'pay') || str_contains($lower, 'submit')) {
            return "💰 **Payment Queries**\n\nYou can:\n• Submit new payment proofs\n• Check payment status\n• View payment history\n• Use referral codes for benefits\n\nVisit the 'Payments' section or let me know what you need help with!";
        }
        
        // Wallet related
        if (str_contains($lower, 'wallet') || str_contains($lower, 'earning') || str_contains($lower, 'withdraw')) {
            return "💵 **Wallet & Earnings**\n\nYour wallet features:\n• Check balance and earnings\n• View referral rewards\n• Request withdrawals (min ₹500)\n• Track transaction history\n\nNeed help with a specific wallet query?";
        }
        
        // Tasks related
        if (str_contains($lower, 'task') || str_contains($lower, 'daily') || str_contains($lower, 'practice')) {
            return "📋 **Daily Tasks**\n\nYour personalized learning tasks:\n• AI-generated practice problems\n• Based on your progress\n• Updated every Friday\n• Track completion and progress\n\nCheck your 'Daily Tasks' section to see today's challenges!";
        }
        
        // Community/Chat
        if (str_contains($lower, 'community') || str_contains($lower, 'chat') || str_contains($lower, 'student')) {
            return "👥 **Community & Networking**\n\nConnect with others:\n• Browse student community\n• Chat with peers\n• Ask for help\n• Share knowledge\n• Form study groups\n\nVisit the 'Community' section to get started!";
        }
        
        // Portfolio
        if (str_contains($lower, 'portfolio') || str_contains($lower, 'project') || str_contains($lower, 'skill')) {
            return "💼 **Portfolio Management**\n\nShowcase your work:\n• Add projects and descriptions\n• List your skills\n• Add achievements\n• Share your public portfolio\n• Customize banner\n\nBuild your portfolio in the 'Portfolio' section!";
        }
        
        // Events
        if (str_contains($lower, 'event') || str_contains($lower, 'workshop') || str_contains($lower, 'program')) {
            return "🗓 **Events & Programs**\n\nStay updated:\n• Browse upcoming events\n• Register for workshops\n• Join programs\n• Track registrations\n\nCheck the 'Events' section for what's coming up!";
        }
        
        // Progress/Stats
        if (str_contains($lower, 'progress') || str_contains($lower, 'stat') || str_contains($lower, 'certificate')) {
            return "📈 **Progress Tracking**\n\nMonitor your journey:\n• View course completion %\n• Track lessons completed\n• Earn certificates\n• See learning stats\n\nYour progress is visible on your dashboard!";
        }
        
        // Referral
        if (str_contains($lower, 'referral') || str_contains($lower, 'refer') || str_contains($lower, 'invite')) {
            return "🎁 **Referral Program**\n\nEarn rewards:\n• Your referral ID: `{$user['referral_id']}`\n• Share with friends\n• Earn when they pay\n• Track in wallet\n\nShare your code and start earning!";
        }
        
        // Greetings
        if (preg_match('/\b(hello|hi|hey|namaste)\b/i', $lower)) {
            return "Hello {$name}! 👋\n\nI'm your **TCM Agent** - here to help you succeed! 🚀\n\nI can assist with:\n📚 Courses & Learning\n💰 Payments & Wallet\n📋 Daily Tasks\n🗓 Events & Programs\n👥 Community & Chat\n💼 Portfolio\n📈 Progress Tracking\n\nWhat would you like to know?";
        }
        
        // Help/What can you do
        if (str_contains($lower, 'help') || str_contains($lower, 'what can') || str_contains($lower, 'features')) {
            return "🤖 **TCM Agent - Your Learning Assistant**\n\nI'm here to help with:\n\n📚 **Learning**\n• Course browsing and enrollment\n• Progress tracking\n• Daily practice tasks\n\n💰 **Financial**\n• Payment submissions\n• Wallet management\n• Referral rewards\n\n👥 **Community**\n• Student networking\n• Group chats\n• Peer help\n\n💼 **Profile**\n• Portfolio building\n• Skill showcasing\n• Achievements\n\nJust ask me anything!";
        }
        
        // Default response - more helpful
        return "Hi {$name}! 👋\n\nI'm not sure I understood that, but I'm here to help!\n\n**Quick Links:**\n📚 [Courses](/student/courses)\n💰 [Payments](/student/payments)\n📋 [Tasks](/student/tasks)\n💵 [Wallet](/student/wallet)\n👥 [Community](/student/community)\n\nOr try asking about:\n• Courses and learning\n• Payments and wallet\n• Daily tasks\n• Events and programs\n• Your portfolio\n\nWhat would you like to know?";
    }
}
