<div class="page-header">
    <div class="page-header-content">
        <div class="chat-user-info">
            <a href="/admin/chat" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div class="user-avatar">
                <?php if (!empty($chatUser['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($chatUser['avatar']); ?>" alt="">
                <?php else: ?>
                    <span><?php echo strtoupper(substr($chatUser['username'], 0, 1)); ?></span>
                <?php endif; ?>
            </div>
            <div class="user-details">
                <h1 class="page-title"><?php echo htmlspecialchars($chatUser['username']); ?></h1>
                <p class="user-email"><?php echo htmlspecialchars($chatUser['email']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="chat-container">
    <div class="chat-messages" id="chat-messages">
        <?php foreach ($messages as $msg): ?>
            <div class="message <?php echo $msg['sender_type']; ?>">
                <div class="message-content">
                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                </div>
                <div class="message-time">
                    <?php echo date('d/m H:i', strtotime($msg['created_at'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <form id="reply-form" class="chat-input-form">
        <input type="text" id="message-input" name="message" placeholder="Nhap tin nhan..." autocomplete="off" required>
        <button type="submit" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
        </button>
    </form>
</div>

<style>
.chat-user-info {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.back-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: var(--radius-md);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    transition: all 0.2s;
}

.back-link:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: var(--text-lg);
    color: var(--primary-color);
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-details {
    flex: 1;
}

.user-details .page-title {
    margin: 0;
    font-size: var(--text-lg);
}

.user-email {
    margin: 0;
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.chat-container {
    max-width: 800px;
    margin: 0 auto;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    display: flex;
    flex-direction: column;
    height: calc(100vh - 250px);
    min-height: 400px;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.message {
    max-width: 70%;
    display: flex;
    flex-direction: column;
}

.message.user {
    align-self: flex-start;
}

.message.admin {
    align-self: flex-end;
}

.message-content {
    padding: var(--space-3);
    border-radius: var(--radius-lg);
    line-height: 1.5;
}

.message.user .message-content {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border-bottom-left-radius: var(--radius-sm);
}

.message.admin .message-content {
    background: var(--primary-color);
    color: white;
    border-bottom-right-radius: var(--radius-sm);
}

.message-time {
    font-size: var(--text-xs);
    color: var(--text-muted);
    margin-top: var(--space-1);
}

.message.admin .message-time {
    text-align: right;
}

.chat-input-form {
    display: flex;
    gap: var(--space-2);
    padding: var(--space-4);
    border-top: 1px solid var(--border-color);
}

.chat-input-form input {
    flex: 1;
    padding: var(--space-3);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: var(--text-base);
}

.chat-input-form input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.chat-input-form button {
    padding: var(--space-3);
    border-radius: var(--radius-lg);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('chat-messages');
    const replyForm = document.getElementById('reply-form');
    const messageInput = document.getElementById('message-input');
    const userId = <?php echo $chatUser['id']; ?>;
    
    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // Send message
    replyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (!message) return;
        
        const formData = new FormData();
        formData.append('message', message);
        
        fetch('/admin/chat/' + userId + '/send', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Add message to UI
                const msgDiv = document.createElement('div');
                msgDiv.className = 'message admin';
                msgDiv.innerHTML = `
                    <div class="message-content">${escapeHtml(message)}</div>
                    <div class="message-time">Vua xong</div>
                `;
                messagesContainer.appendChild(msgDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                messageInput.value = '';
            } else {
                alert(data.error || 'Co loi xay ra');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Loi ket noi');
        });
    });
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    }
    
    // Poll for new messages every 5 seconds
    let lastMessageId = <?php echo !empty($messages) ? end($messages)['id'] : 0; ?>;
    
    setInterval(function() {
        fetch('/api/admin/chat/' + userId + '/messages?last_id=' + lastMessageId)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        if (msg.id > lastMessageId) {
                            const msgDiv = document.createElement('div');
                            msgDiv.className = 'message ' + msg.sender_type;
                            msgDiv.innerHTML = `
                                <div class="message-content">${escapeHtml(msg.message)}</div>
                                <div class="message-time">${new Date(msg.created_at).toLocaleString('vi-VN', {day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'})}</div>
                            `;
                            messagesContainer.appendChild(msgDiv);
                            lastMessageId = msg.id;
                        }
                    });
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            })
            .catch(err => console.error('Poll error:', err));
    }, 5000);
});
</script>
