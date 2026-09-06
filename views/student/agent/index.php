<?php
// TCM Agent - AI Assistant for students
$studentName = $user['name'] ?? 'Student';
?>
<style>
.agent-container {
    max-width: 900px;
    margin: 0 auto;
}
.agent-chat {
    background: #fff;
    border: 1px solid #ececec;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px);
    min-height: 500px;
}
.agent-header {
    padding: 16px 20px;
    border-bottom: 1px solid #ececec;
    display: flex;
    align-items: center;
    gap: 12px;
}
.agent-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #111;
    display: grid;
    place-items: center;
    font-size: 1.3rem;
    color: #fff;
}
.agent-info {
    flex: 1;
}
.agent-info h3 {
    font-size: 1rem;
    font-weight: 800;
    color: #111;
    margin: 0;
}
.agent-info p {
    font-size: 0.75rem;
    color: #888;
    margin: 2px 0 0;
}
.agent-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.agent-message {
    display: flex;
    gap: 10px;
    animation: fadeIn 0.3s ease;
}
.agent-message.user {
    flex-direction: row-reverse;
}
.agent-message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    font-size: 0.8rem;
    font-weight: 700;
}
.agent-message.agent .agent-message-avatar {
    background: #111;
    color: #fff;
}
.agent-message.user .agent-message-avatar {
    background: #111;
    color: #fff;
}
.agent-message-bubble {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 16px;
    font-size: 0.88rem;
    line-height: 1.5;
    white-space: pre-wrap;
}
.agent-message.agent .agent-message-bubble {
    background: #f5f5f5;
    border-bottom-left-radius: 4px;
    color: #111;
}
.agent-message.user .agent-message-bubble {
    background: #111;
    border-bottom-right-radius: 4px;
    color: #fff;
}
.agent-message-time {
    font-size: 0.68rem;
    color: #aaa;
    margin-top: 4px;
}
.agent-input-area {
    padding: 16px 20px;
    border-top: 1px solid #ececec;
    background: #fafafa;
}
.agent-input-form {
    display: flex;
    gap: 10px;
    align-items: center;
}
.agent-input {
    flex: 1;
    padding: 12px 16px;
    border: 1.5px solid #e5e5e5;
    border-radius: 24px;
    font-size: 0.88rem;
    font-family: inherit;
    transition: border-color 0.2s;
}
.agent-input:focus {
    outline: none;
    border-color: #111;
}
.agent-send-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #111;
    color: #fff;
    border: none;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 1.1rem;
    transition: all 0.2s;
}
.agent-send-btn:hover {
    background: #333;
    transform: scale(1.05);
}
.agent-send-btn:active {
    transform: scale(0.95);
}
.agent-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.agent-typing {
    display: flex;
    gap: 4px;
    padding: 8px 12px;
}
.agent-typing span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #aaa;
    animation: typing 1.4s infinite;
}
.agent-typing span:nth-child(2) {
    animation-delay: 0.2s;
}
.agent-typing span:nth-child(3) {
    animation-delay: 0.4s;
}
.agent-welcome {
    text-align: center;
    padding: 40px 20px;
    color: #aaa;
}
.agent-welcome i {
    font-size: 3rem;
    margin-bottom: 16px;
    opacity: 0.3;
}
.agent-welcome h3 {
    font-size: 1.1rem;
    font-weight: 800;
    color: #111;
    margin-bottom: 8px;
}
.agent-welcome p {
    font-size: 0.88rem;
    margin-bottom: 20px;
}
.agent-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    max-width: 500px;
    margin: 0 auto;
}
.agent-suggestion {
    padding: 8px 14px;
    background: #f5f5f5;
    border: 1px solid #e5e5e5;
    border-radius: 20px;
    font-size: 0.78rem;
    cursor: pointer;
    transition: all 0.2s;
}
.agent-suggestion:hover {
    background: #111;
    color: #fff;
    border-color: #111;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-8px); }
}
@media (max-width: 600px) {
    .agent-message-bubble {
        max-width: 85%;
    }
}
</style>

<div class="agent-container">
    <div class="agent-chat">
        <!-- Header -->
        <div class="agent-header">
            <div class="agent-avatar">
                <i class="bi bi-robot"></i>
            </div>
            <div class="agent-info">
                <h3>TCM Agent</h3>
                <p>Your AI learning assistant · Always ready to help</p>
            </div>
            <span style="width: 10px; height: 10px; background: #22c55e; border-radius: 50%; 
                         border: 2px solid #fff; box-shadow: 0 0 0 2px #dcfce7;" 
                  title="Online"></span>
        </div>

        <!-- Messages -->
        <div class="agent-messages" id="agentMessages">
            <!-- Welcome Screen -->
            <div class="agent-welcome" id="agentWelcome">
                <i class="bi bi-chat-dots-fill"></i>
                <h3>Welcome to TCM Agent, <?= e($studentName) ?>! 👋</h3>
                <p>I'm here to help you with courses, payments, wallet, events, and more.</p>
                <div class="agent-suggestions">
                    <button class="agent-suggestion" onclick="sendQuickMessage('How do I enroll in a course?')">
                        📚 Enroll in courses
                    </button>
                    <button class="agent-suggestion" onclick="sendQuickMessage('How can I check my wallet balance?')">
                        💰 Check wallet
                    </button>
                    <button class="agent-suggestion" onclick="sendQuickMessage('Tell me about upcoming events')">
                        🗓 Upcoming events
                    </button>
                    <button class="agent-suggestion" onclick="sendQuickMessage('How do I submit a payment?')">
                        💳 Submit payment
                    </button>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="agent-input-area">
            <form class="agent-input-form" id="agentForm">
                <input type="text" 
                       class="agent-input" 
                       id="agentInput" 
                       placeholder="Ask me anything..."
                       autocomplete="off">
                <button type="submit" class="agent-send-btn" id="sendBtn">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const messagesEl = document.getElementById('agentMessages');
    const welcomeEl = document.getElementById('agentWelcome');
    const form = document.getElementById('agentForm');
    const input = document.getElementById('agentInput');
    const sendBtn = document.getElementById('sendBtn');
    
    let isFirstMessage = true;

    function addMessage(text, isUser) {
        if (isFirstMessage && welcomeEl) {
            welcomeEl.remove();
            isFirstMessage = false;
        }

        const msgDiv = document.createElement('div');
        msgDiv.className = 'agent-message ' + (isUser ? 'user' : 'agent');
        
        const avatar = document.createElement('div');
        avatar.className = 'agent-message-avatar';
        avatar.textContent = isUser ? '<?= strtoupper(substr($studentName, 0, 1)) ?>' : '🤖';
        
        const bubbleWrap = document.createElement('div');
        
        const bubble = document.createElement('div');
        bubble.className = 'agent-message-bubble';
        bubble.textContent = text;
        
        const time = document.createElement('div');
        time.className = 'agent-message-time';
        time.textContent = new Date().toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        bubbleWrap.appendChild(bubble);
        bubbleWrap.appendChild(time);
        
        msgDiv.appendChild(avatar);
        msgDiv.appendChild(bubbleWrap);
        
        messagesEl.appendChild(msgDiv);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function showTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'agent-message agent';
        typingDiv.id = 'typingIndicator';
        
        const avatar = document.createElement('div');
        avatar.className = 'agent-message-avatar';
        avatar.textContent = '🤖';
        
        const typing = document.createElement('div');
        typing.className = 'agent-typing';
        typing.innerHTML = '<span></span><span></span><span></span>';
        
        typingDiv.appendChild(avatar);
        typingDiv.appendChild(typing);
        
        messagesEl.appendChild(typingDiv);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function hideTyping() {
        const typing = document.getElementById('typingIndicator');
        if (typing) typing.remove();
    }

    async function sendMessage(message) {
        if (!message.trim()) return;
        
        addMessage(message, true);
        input.value = '';
        input.disabled = true;
        sendBtn.disabled = true;
        
        showTyping();
        
        try {
            const response = await fetch('<?= base_url('/student/agent/chat') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message })
            });
            
            const data = await response.json();
            
            hideTyping();
            
            if (data.success && data.data.message) {
                setTimeout(() => addMessage(data.data.message, false), 300);
            } else {
                addMessage('Sorry, I encountered an error. Please try again.', false);
            }
        } catch (error) {
            hideTyping();
            addMessage('Sorry, I am unable to connect right now. Please try again later.', false);
        } finally {
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();
        }
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage(input.value);
    });

    window.sendQuickMessage = function(message) {
        input.value = message;
        sendMessage(message);
    };

    // Focus input on load
    input.focus();
})();
</script>
