<!-- Chat Bubble Button -->
<!-- <div id="chat-bubble" class="fixed bottom-10 right-12 z-40">
    <button onclick="toggleChat()"
            class="aspect-square w-15 bg-blue-900 hover:bg-blue-800 text-white p-4 text-2xl rounded-full border-none shadow-lg hover:shadow-xl cursor-pointer transition-all duration-300 ease-out relative overflow-hidden transform hover:-translate-y-0.5 hover:scale-105 active:translate-y-0 group"
            style="background-color: #002c76;">
        <span id="chat-icon" class="inline-block">💬</span>
        <span id="close-icon" class="hidden p-1.5 rounded-full">✕</span>
        <div class="absolute inset-0 bg-white bg-opacity-10 transform -translate-x-full transition-transform duration-300 ease-in-out group-hover:translate-x-0"></div>
    </button>
</div> -->
<!-- Chat Window -->
<div id="chat-window"
     class="chat-window fixed bottom-32 right-12 w-96 h-[500px] bg-white border border-gray-200 rounded-2xl shadow-2xl flex flex-col opacity-0 transform translate-y-8 scale-95 pointer-events-none transition-all duration-400 ease-out z-40 backdrop-blur-sm">
   
    <!-- Header -->
    <div class="flex items-center gap-3 p-4 rounded-t-2xl shadow-sm" style="background: #002c76">
    <div class="relative">
        <img src="{{ asset('images/dilg-chatbot.png') }}"
             alt="AI Avatar"
             class="w-10 h-10 rounded-full border-2 border-white border-opacity-20 transition-transform duration-300 hover:scale-110"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="absolute bottom-0.5 right-0.5 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
    </div>
    <div class="flex-1">
        <div class="font-semibold text-white text-base">A.I.na</div>
        <div class="text-xs text-white text-opacity-80 mt-0.5" id="status-text">Online • Ready to help</div>
    </div>
    <button onclick="clearChat()"
            class="bg-white bg-opacity-10 hover:bg-opacity-20 text-white text-opacity-80 border-none p-2 rounded-lg cursor-pointer text-sm transition-all duration-200"
            title="Clear Chat">
        <i data-feather="trash-2" class="w-4 h-4"></i>
    </button>
</div>
    <!-- Messages Container -->
    <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3 bg-gradient-to-b from-gray-50 to-white custom-scrollbar"
         id="chat-messages">
        <!-- Messages will be inserted here -->
    </div>
    <!-- Typing Indicator -->
    <div id="typing-indicator" class="hidden px-4 pb-3">
    <div class="flex items-center gap-2">
        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs overflow-hidden bg-white">
            <img src="{{ asset('images/dilg-chatbot.png') }}" alt="Chatbot" class="w-full h-full object-cover" />
        </div>
        <div class="bg-gray-200 px-3 py-2 rounded-2xl max-w-fit flex items-center gap-1.5">
            <div class="flex gap-1">
                <span class="w-2 h-2 bg-gray-500 rounded-full animate-pulse typing-dot"></span>
                <span class="w-2 h-2 bg-gray-500 rounded-full animate-pulse typing-dot animation-delay-200"></span>
                <span class="w-2 h-2 bg-gray-500 rounded-full animate-pulse typing-dot animation-delay-400"></span>
            </div>
        </div>
    </div>
</div>
    <!-- Input Container -->
    <div class="p-3 border-t border-gray-200 bg-white rounded-b-2xl">
        <div class="flex gap-2 bg-gray-50 border border-gray-200 rounded-xl p-1 transition-all duration-200 focus-within:bg-white"
             id="input-container"
             style="focus-within:border-color: #002c76; focus-within:box-shadow: 0 0 0 3px rgba(0, 44, 118, 0.1);">
            <input type="text"
                   id="chat-input"
                   placeholder="Type your message..."
                   class="flex-1 px-4 py-3 border-none bg-transparent text-sm outline-none text-gray-700 placeholder-gray-400">
            <button onclick="sendMessage()"
                    class="px-4 py-3 text-white border-none rounded-lg text-sm cursor-pointer transition-all duration-200 flex items-center gap-1.5 hover:scale-105 active:scale-100"
                    style="background: linear-gradient(135deg, #002C76 0%, #0052CC 100%);">
                <span>Send</span>
                <span class="text-xs">➤</span>
            </button>
        </div>
        <div class="text-xs text-gray-400 mt-2 text-center">Press Enter to send • Powered by AI</div>
    </div>
</div>
<style>
.chat-window.open {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.message-bubble {
    animation: messageSlide 0.3s ease-out;
}
@keyframes messageSlide {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.typing-dot {
    animation: typing-pulse 1.4s infinite ease-in-out both;
}
.animation-delay-200 {
    animation-delay: 0.2s;
}
.animation-delay-400 {
    animation-delay: 0.4s;
}
@keyframes typing-pulse {
    0%, 80%, 100% {
        opacity: 0.3;
        transform: scale(0.8);
    }
    40% {
        opacity: 1;
        transform: scale(1);
    }
}
#input-container:focus-within {
    border-color: #002c76 !important;
    box-shadow: 0 0 0 3px rgba(0, 44, 118, 0.1) !important;
}

/* Mobile responsiveness - Popup bubble optimization */
@media (max-width: 768px) {
    /* Chat bubble positioning and size */
    #chat-bubble {
        bottom: 20px !important;
        right: 20px !important;
    }
    
    #chat-bubble button {
        padding: 14px !important;
        font-size: 20px !important;
        width: 60px !important;
        height: 60px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    #chat-bubble button:active {
        transform: scale(0.95) !important;
    }
    
    /* Chat window - Mobile popup optimization */
    .chat-window {
        width: calc(100vw - 32px) !important;
        height: 80vh !important;
        max-height: 600px !important;
        right: 16px !important;
        bottom: 90px !important;
        left: 16px !important;
        border-radius: 16px !important;
    }
    
    /* Header adjustments */
    .chat-window > div:first-child {
        border-radius: 16px 16px 0 0 !important;
        padding: 12px 16px !important;
    }
    
    /* Messages container */
    #chat-messages {
        padding: 12px 16px !important;
        gap: 10px !important;
    }
    
    /* Message bubbles - Better mobile sizing */
    .message-bubble .bg-blue-500,
    .message-bubble .bg-gray-200 {
        max-width: calc(100vw - 120px) !important;
        font-size: 14px !important;
        line-height: 1.4 !important;
        padding: 10px 14px !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
    }
    
    /* Avatar size adjustment */
    .message-bubble .w-7 {
        width: 28px !important;
        height: 28px !important;
        flex-shrink: 0 !important;
    }
    
    /* Typing indicator */
    #typing-indicator {
        padding: 0 16px 10px 16px !important;
    }
    
    /* Input container - Enhanced mobile experience */
    .chat-window > div:last-child {
        padding: 12px 16px !important;
        padding-bottom: 16px !important;
        border-radius: 0 0 16px 16px !important;
    }
    
    #input-container {
        border-radius: 20px !important;
        padding: 2px !important;
        min-height: 44px !important;
    }
    
    #chat-input {
        padding: 10px 16px !important;
        font-size: 16px !important; /* Prevents zoom on iOS */
        line-height: 1.4 !important;
        border-radius: 18px !important;
    }
    
    #input-container button {
        padding: 8px 16px !important;
        border-radius: 18px !important;
        font-size: 13px !important;
        white-space: nowrap !important;
        min-width: 60px !important;
    }
    
    /* Clear button in header */
    .chat-window button[title="Clear Chat"] {
        padding: 6px !important;
        font-size: 12px !important;
    }
    
    /* Scrollbar for mobile */
    .custom-scrollbar::-webkit-scrollbar {
        width: 3px !important;
    }
    
    /* Status text adjustments */
    #status-text {
        font-size: 11px !important;
    }
    
    /* Bottom text */
    .chat-window .text-xs.text-gray-400 {
        font-size: 10px !important;
        margin-top: 6px !important;
    }
}

/* Extra small mobile devices */
@media (max-width: 375px) {
    .message-bubble .bg-blue-500,
    .message-bubble .bg-gray-200 {
        max-width: calc(100vw - 100px) !important;
        font-size: 13px !important;
        padding: 8px 12px !important;
    }
    
    #chat-messages {
        padding: 10px 12px !important;
    }
    
    .chat-window > div:first-child,
    .chat-window > div:last-child {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }
    
    .chat-window {
        width: calc(100vw - 24px) !important;
        right: 12px !important;
        left: 12px !important;
    }
}

/* Landscape mobile orientation */
@media (max-width: 768px) and (orientation: landscape) {
    .chat-window {
        height: 70vh !important;
        max-height: 400px !important;
    }
    
    .chat-window > div:first-child {
        padding: 10px 16px !important;
    }
    
    #chat-messages {
        padding: 10px 16px !important;
    }
    
    .chat-window > div:last-child {
        padding: 10px 16px !important;
    }
}
</style>
<script>
// Initialize Feather icons if available
if (typeof feather !== 'undefined') {
    feather.replace();
}

document.addEventListener('DOMContentLoaded', () => {
    // Load chat history
    const history = JSON.parse(localStorage.getItem('chatHistory') || '[]');
    history.forEach(h => appendMessage('', h.text, h.isUser));
    
    // Enhanced input binding
    const input = document.getElementById("chat-input");
    input.addEventListener("keypress", function(event) {
        if (event.key === "Enter" && !event.shiftKey) {
            sendMessage();
            event.preventDefault();
        }
    });
    
    // Input focus effects
    input.addEventListener('focus', () => {
        document.getElementById('status-text').textContent = 'Listening...';
    });
   
    input.addEventListener('blur', () => {
        document.getElementById('status-text').textContent = 'Online • Ready to help';
    });
    
    // Mobile optimizations without body scroll blocking
    
    // Handle orientation changes
    window.addEventListener('orientationchange', function() {
        setTimeout(() => {
            const chatMessages = document.getElementById('chat-messages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }, 100);
    });
});

const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

function toggleChat() {
    const chat = document.getElementById('chat-window');
    const chatIcon = document.getElementById('chat-icon');
    const closeIcon = document.getElementById('close-icon');
    const alreadyOpened = chat.classList.contains('open');
    
    chat.classList.toggle('open');
    
    // Animate button icons
    if (alreadyOpened) {
        chatIcon.classList.remove('hidden');
        closeIcon.classList.add('hidden');
    } else {
        chatIcon.classList.add('hidden');
        closeIcon.classList.remove('hidden');
    }
    
    if (!alreadyOpened) {
        setTimeout(() => {
            document.getElementById('chat-input').focus();
        }, 400);
        
        // Show welcome message for first-time users
        const history = JSON.parse(localStorage.getItem('chatHistory') || '[]');
        if (history.length === 0) {
            setTimeout(() => {
                showTyping(true);
                setTimeout(() => {
                    showTyping(false);
                    appendMessage("AI", "👋 Hello there! I'm A.I.nna, your DILG Assistant. I'm here to help you with any questions or information you need. How can I assist you today?", false);
                    saveHistory("👋 Hello there! I'm A.I.nna, your DILG Assistant. I'm here to help you with any questions or information you need. How can I assist you today?", false);
                }, 1500);
            }, 500);
        }
    }
}

function sendMessage() {
    const input = document.getElementById("chat-input");
    const message = input.value.trim();
    if (!message) return;
    
    appendMessage("You", message, true);
    saveHistory(message, true);
    input.value = "";
    showTyping(true);
    
    // Update status
    document.getElementById('status-text').textContent = 'Processing...';
    
    fetch("/chat", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ message })
    })
    .then(async res => {
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch {
            showTyping(false);
            appendMessage("AI", "⚠️ I'm having trouble connecting right now. Please try again in a moment.", false);
            document.getElementById('status-text').textContent = 'Connection error';
            return;
        }
        
        showTyping(false);
        document.getElementById('status-text').textContent = 'Online • Ready to help';
        
        const reply = data.candidates?.[0]?.content?.parts?.[0]?.text ?? 'I apologize, but I didn\'t receive a proper response. Could you please try asking again?';
       
        setTimeout(() => {
            appendMessage("AI", reply, false);
            saveHistory(reply, false);
        }, 300);
    })
    .catch(err => {
        showTyping(false);
        document.getElementById('status-text').textContent = 'Offline';
        appendMessage("AI", "⚠️ I'm currently unable to respond. Please check your connection and try again.", false);
    });
}

function appendMessage(sender, text, isUser) {
    const chatBox = document.getElementById("chat-messages");
    const bubble = document.createElement("div");
   
    const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    // Enhanced text formatting
    const htmlText = text
        .replace(/\*\*([^*]+)\*\*/g, '<strong class="text-gray-900">$1</strong>')
        .replace(/\*([^*]+)\*/g, '<em class="text-gray-600">$1</em>')
        .replace(/\n/g, '<br>')
        .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" class="text-blue-600 hover:underline">$1</a>');
    
    bubble.className = 'message-bubble mb-1';
   
    if (isUser) {
        // User message
        bubble.innerHTML = `
            <div class="flex flex-col items-end">
                <div class="bg-blue-500 text-white px-3 py-2 rounded-2xl max-w-60 break-words text-sm leading-relaxed">
                    ${htmlText}
                </div>
                <div class="text-xs text-gray-500 mt-1 opacity-70">${timestamp}</div>
            </div>
        `;
        bubble.className += ' self-end';
    } else {
        // AI message
        bubble.innerHTML = `
            <div class="flex items-start gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden"
                     style="background: linear-gradient(135deg, #002C76, #0052CC);">
                    <img src="{{ asset('images/dilg-chatbot.png') }}"
                         alt="Chatbot Icon"
                         class="w-full h-full object-cover" />
                </div>
                <div class="flex flex-col">
                    <div class="bg-gray-200 text-gray-900 px-3 py-2 rounded-2xl max-w-60 break-words text-sm leading-relaxed">
                        ${htmlText}
                    </div>
                    <div class="text-xs text-gray-500 mt-1 ml-3 opacity-70">${timestamp}</div>
                </div>
            </div>
        `;
        bubble.className += ' self-start w-full';
    }
    
    chatBox.appendChild(bubble);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function showTyping(show = true) {
    const indicator = document.getElementById("typing-indicator");
    if (show) {
        indicator.classList.remove('hidden');
    } else {
        indicator.classList.add('hidden');
    }
    
    if (show) {
        document.getElementById("chat-messages").scrollTop = document.getElementById("chat-messages").scrollHeight;
    }
}

function saveHistory(text, isUser) {
    const history = JSON.parse(localStorage.getItem('chatHistory') || '[]');
    history.push({ text, isUser });
    
    // Limit history to last 50 messages
    if (history.length > 50) {
        history.splice(0, history.length - 50);
    }
    
    localStorage.setItem('chatHistory', JSON.stringify(history));
}

function clearChat() {
    if (confirm('Are you sure you want to clear the chat history?')) {
        localStorage.removeItem('chatHistory');
        document.getElementById('chat-messages').innerHTML = '';
        setTimeout(() => {
            appendMessage("AI", "👋 Chat cleared! How can I help you today?", false);
            saveHistory("👋 Chat cleared! How can I help you today?", false);
        }, 300);
    }
}

// Message hover effects
document.addEventListener('mouseover', function(e) {
    if (e.target.closest('.message-bubble')) {
        const timestamps = e.target.closest('.message-bubble').querySelectorAll('.opacity-70');
        timestamps.forEach(timestamp => {
            timestamp.classList.remove('opacity-70');
            timestamp.classList.add('opacity-100');
        });
    }
});

document.addEventListener('mouseout', function(e) {
    if (e.target.closest('.message-bubble')) {
        const timestamps = e.target.closest('.message-bubble').querySelectorAll('.opacity-100');
        timestamps.forEach(timestamp => {
            timestamp.classList.remove('opacity-100');
            timestamp.classList.add('opacity-70');
        });
    }
});

// Mobile orientation handling
window.addEventListener('orientationchange', function() {
    setTimeout(() => {
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }, 100);
});
</script>