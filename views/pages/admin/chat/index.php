<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            Live Chat
        </h1>
        <p class="page-description">Quan ly cuoc tro chuyen voi users</p>
    </div>
</div>

<div class="chat-admin-container">
    <?php if (empty($conversations)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <h3>Chua co cuoc tro chuyen nao</h3>
            <p>Khi users gui tin nhan, chung se hien thi o day</p>
        </div>
    <?php else: ?>
        <div class="conversations-list">
            <?php foreach ($conversations as $conv): ?>
                <a href="/admin/chat/<?php echo $conv['id']; ?>" class="conversation-item <?php echo $conv['unread_count'] > 0 ? 'has-unread' : ''; ?>">
                    <div class="conv-avatar">
                        <?php if (!empty($conv['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($conv['avatar']); ?>" alt="">
                        <?php else: ?>
                            <span><?php echo strtoupper(substr($conv['username'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="conv-content">
                        <div class="conv-header">
                            <span class="conv-username"><?php echo htmlspecialchars($conv['username']); ?></span>
                            <span class="conv-time"><?php echo date('d/m H:i', strtotime($conv['last_message_at'])); ?></span>
                        </div>
                        <div class="conv-preview">
                            <?php echo htmlspecialchars(mb_substr($conv['last_message'] ?? '', 0, 50)); ?>
                            <?php if (strlen($conv['last_message'] ?? '') > 50): ?>...<?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($conv['unread_count'] > 0): ?>
                        <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.chat-admin-container {
    max-width: 800px;
    margin: 0 auto;
}

.conversations-list {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.conversation-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    border-bottom: 1px solid var(--border-color);
    text-decoration: none;
    color: inherit;
    transition: background 0.2s;
}

.conversation-item:last-child {
    border-bottom: none;
}

.conversation-item:hover {
    background: var(--bg-secondary);
}

.conversation-item.has-unread {
    background: rgba(var(--primary-rgb), 0.05);
}

.conv-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: var(--primary-color);
    overflow: hidden;
    flex-shrink: 0;
}

.conv-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.conv-content {
    flex: 1;
    min-width: 0;
}

.conv-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-1);
}

.conv-username {
    font-weight: 600;
    color: var(--text-primary);
}

.conv-time {
    font-size: var(--text-xs);
    color: var(--text-muted);
}

.conv-preview {
    font-size: var(--text-sm);
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge {
    background: var(--danger-color);
    color: white;
    font-size: var(--text-xs);
    font-weight: 600;
    padding: var(--space-1) var(--space-2);
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
}

.empty-state {
    text-align: center;
    padding: var(--space-12);
    background: var(--bg-secondary);
    border-radius: var(--radius-xl);
}

.empty-state svg {
    color: var(--text-muted);
    margin-bottom: var(--space-4);
}

.empty-state h3 {
    margin: 0 0 var(--space-2) 0;
    color: var(--text-primary);
}

.empty-state p {
    margin: 0;
    color: var(--text-secondary);
}
</style>
