<?php if (isset($_SESSION['user_id'])): ?>
<!-- Live Chat Widget -->
<div class="chat-widget" id="chat-widget">
    <!-- Chat Toggle Button -->
    <button class="chat-toggle-btn" id="chat-toggle">
        <svg class="chat-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
        <span class="chat-badge" id="chat-badge" style="display: none;">0</span>
    </button>
    
    <!-- Chat Window -->
    <div class="chat-window" id="chat-window" style="display: none;">
        <div class="chat-header">
            <div class="chat-header-info">
                <span class="chat-status" id="chat-status">
                    <span class="status-dot offline"></span>
                    <span class="status-text">Offline</span>
                </span>
                <h4>Ho tro truc tuyen</h4>
            </div>
            <button class="chat-minimize" id="chat-minimize">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <div class="chat-welcome">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <p>Xin chao! Chung toi co the giup gi cho ban?</p>
            </div>
        </div>
        
        <form class="chat-input" id="chat-form">
            <input type="text" id="chat-input" placeholder="Nhap tin nhan..." autocomplete="off">
            <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </form>
    </div>
</div>

<style>
.chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

@media (max-width: 768px) {
    .chat-widget {
        bottom: 80px; /* Above bottom nav */
    }
}

.chat-toggle-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border: none;
    color: white;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}

.chat-toggle-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(var(--primary-rgb), 0.5);
}

.chat-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: var(--danger-color);
    color: white;
    font-size: 11px;
    font-weight: 600;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
}

.chat-window {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 350px;
    max-width: calc(100vw - 40px);
    height: 450px;
    max-height: calc(100vh - 150px);
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-3) var(--space-4);
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
}

.chat-header-info h4 {
    margin: 0;
    font-size: var(--text-base);
}

.chat-status {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--text-xs);
    opacity: 0.9;
    margin-bottom: var(--space-1);
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-dot.online { background: #4ade80; }
.status-dot.offline { background: #9ca3af; }

.chat-minimize {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: var(--radius-md);
    padding: var(--space-1);
    color: white;
    cursor: pointer;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: var(--space-3);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.chat-welcome {
    text-align: center;
    color: var(--text-secondary);
    padding: var(--space-4);
}

.chat-welcome svg {
    color: var(--text-muted);
    margin-bottom: var(--space-2);
}

.chat-welcome p {
    margin: 0;
}

.chat-message {
    max-width: 80%;
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-lg);
    font-size: var(--text-sm);
    line-height: 1.4;
}

.chat-message.user {
    align-self: flex-end;
    background: var(--primary-color);
    color: white;
    border-bottom-right-radius: var(--radius-sm);
}

.chat-message.admin {
    align-self: flex-start;
    background: var(--bg-secondary);
    color: var(--text-primary);
    border-bottom-left-radius: var(--radius-sm);
}

.chat-message-time {
    font-size: var(--text-xs);
    opacity: 0.7;
    margin-top: var(--space-1);
}

.chat-input {
    display: flex;
    gap: var(--space-2);
    padding: var(--space-3);
    border-top: 1px solid var(--border-color);
}

.chat-input input {
    flex: 1;
    padding: var(--space-2) var(--space-3);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: var(--text-sm);
}

.chat-input input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.chat-input button {
    padding: var(--space-2) var(--space-3);
    border: none;
    border-radius: var(--radius-lg);
    background: var(--primary-color);
    color: white;
    cursor: pointer;
    transition: background 0.2s;
}

.chat-input button:hover {
    background: var(--primary-dark);
}
</style>

<script>
(function() {
    const chatWidget = document.getElementById('chat-widget');
    const chatToggle = document.getElementById('chat-toggle');
    const chatWindow = document.getElementById('chat-window');
    const chatMinimize = document.getElementById('chat-minimize');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatBadge = document.getElementById('chat-badge');
    const chatStatus = document.getElementById('chat-status');
    const chatIcon = chatToggle.querySelector('.chat-icon');
    const closeIcon = chatToggle.querySelector('.close-icon');
    
    let isOpen = false;
    let lastMessageId = 0;
    
    // Toggle chat window
    chatToggle.addEventListener('click', function() {
        isOpen = !isOpen;
        chatWindow.style.display = isOpen ? 'flex' : 'none';
        chatIcon.style.display = isOpen ? 'none' : 'block';
        closeIcon.style.display = isOpen ? 'block' : 'none';
        
        if (isOpen) {
            loadMessages();
            chatInput.focus();
            chatBadge.style.display = 'none';
        }
    });
    
    chatMinimize.addEventListener('click', function() {
        isOpen = false;
        chatWindow.style.display = 'none';
        chatIcon.style.display = 'block';
        closeIcon.style.display = 'none';
    });
    
    // Send message
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add message to UI immediately
        addMessage(message, 'user');
        chatInput.value = '';
        
        // Send to server
        const formData = new FormData();
        formData.append('message', message);
        
        fetch('/api/chat/send', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                lastMessageId = data.message_id;
            }
        })
        .catch(err => console.error('Send error:', err));
    });
    
    function addMessage(text, type) {
        const welcome = chatMessages.querySelector('.chat-welcome');
        if (welcome) welcome.remove();
        
        const div = document.createElement('div');
        div.className = 'chat-message ' + type;
        div.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function loadMessages() {
        fetch('/api/chat/messages')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.messages) {
                    const welcome = chatMessages.querySelector('.chat-welcome');
                    if (welcome && data.messages.length > 0) welcome.remove();
                    
                    data.messages.forEach(msg => {
                        if (msg.id > lastMessageId) {
                            addMessage(msg.message, msg.sender_type);
                            lastMessageId = msg.id;
                        }
                    });
                }
            })
            .catch(err => console.error('Load error:', err));
    }
    
    function checkStatus() {
        fetch('/api/chat/status')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const dot = chatStatus.querySelector('.status-dot');
                    const text = chatStatus.querySelector('.status-text');
                    
                    dot.className = 'status-dot ' + (data.is_online ? 'online' : 'offline');
                    text.textContent = data.is_online ? 'Online' : 'Offline';
                    
                    if (data.unread_count > 0 && !isOpen) {
                        chatBadge.textContent = data.unread_count;
                        chatBadge.style.display = 'flex';
                    }
                }
            })
            .catch(err => console.error('Status error:', err));
    }
    
    // Poll for new messages and status
    setInterval(function() {
        checkStatus();
        if (isOpen) {
            fetch('/api/chat/messages?last_id=' + lastMessageId)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.messages) {
                        data.messages.forEach(msg => {
                            if (msg.id > lastMessageId) {
                                addMessage(msg.message, msg.sender_type);
                                lastMessageId = msg.id;
                            }
                        });
                    }
                })
                .catch(err => console.error('Poll error:', err));
        }
    }, 5000);
    
    // Initial status check
    checkStatus();
})();
</script>
<?php endif; ?>
