<?php if (isset($_SESSION['user_id'])): ?>
<!-- Quick Search / Command Palette Modal -->
<div class="search-modal-overlay" id="search-modal" style="display: none;">
    <div class="search-modal">
        <div class="search-input-wrapper">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="search-input" placeholder="Tim kiem trang, API keys, giao dich..." autocomplete="off">
            <kbd class="search-shortcut">ESC</kbd>
        </div>
        
        <div class="search-results" id="search-results">
            <!-- Recent searches -->
            <div class="search-section" id="recent-section">
                <div class="search-section-title">Tim kiem gan day</div>
                <div class="search-items" id="recent-items"></div>
            </div>
            
            <!-- Quick actions -->
            <div class="search-section" id="quick-actions">
                <div class="search-section-title">Thao tac nhanh</div>
                <div class="search-items">
                    <a href="/keys/create" class="search-item">
                        <span class="item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </span>
                        <span class="item-text">Tao API Key moi</span>
                        <span class="item-shortcut">Ctrl+N</span>
                    </a>
                    <a href="/dashboard" class="search-item">
                        <span class="item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </span>
                        <span class="item-text">Dashboard</span>
                        <span class="item-shortcut">Ctrl+D</span>
                    </a>
                    <a href="/billing/deposit" class="search-item">
                        <span class="item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </span>
                        <span class="item-text">Nap tien</span>
                    </a>
                    <a href="/tickets/create" class="search-item">
                        <span class="item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </span>
                        <span class="item-text">Tao ticket ho tro</span>
                    </a>
                </div>
            </div>
            
            <!-- Search results sections -->
            <div class="search-section" id="pages-section" style="display: none;">
                <div class="search-section-title">Trang</div>
                <div class="search-items" id="pages-items"></div>
            </div>
            
            <div class="search-section" id="keys-section" style="display: none;">
                <div class="search-section-title">API Keys</div>
                <div class="search-items" id="keys-items"></div>
            </div>
            
            <div class="search-section" id="transactions-section" style="display: none;">
                <div class="search-section-title">Giao dich</div>
                <div class="search-items" id="transactions-items"></div>
            </div>
            
            <div class="search-section" id="tickets-section" style="display: none;">
                <div class="search-section-title">Tickets</div>
                <div class="search-items" id="tickets-items"></div>
            </div>
            
            <div class="search-no-results" id="no-results" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <p>Khong tim thay ket qua</p>
            </div>
        </div>
        
        <div class="search-footer">
            <span><kbd>Enter</kbd> de chon</span>
            <span><kbd>Arrow</kbd> de di chuyen</span>
            <span><kbd>Esc</kbd> de dong</span>
        </div>
    </div>
</div>

<style>
.search-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 10000;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 15vh;
}

.search-modal {
    width: 100%;
    max-width: 600px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

.search-input-wrapper {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    border-bottom: 1px solid var(--border-color);
}

.search-icon {
    color: var(--text-muted);
    flex-shrink: 0;
}

.search-input-wrapper input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: var(--text-lg);
    color: var(--text-primary);
    outline: none;
}

.search-input-wrapper input::placeholder {
    color: var(--text-muted);
}

.search-shortcut {
    padding: var(--space-1) var(--space-2);
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: var(--text-xs);
    color: var(--text-muted);
}

.search-results {
    max-height: 400px;
    overflow-y: auto;
    padding: var(--space-2);
}

.search-section {
    margin-bottom: var(--space-3);
}

.search-section:last-child {
    margin-bottom: 0;
}

.search-section-title {
    font-size: var(--text-xs);
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    padding: var(--space-2) var(--space-3);
}

.search-items {
    display: flex;
    flex-direction: column;
}

.search-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    text-decoration: none;
    transition: background 0.1s;
    cursor: pointer;
}

.search-item:hover,
.search-item.active {
    background: var(--bg-secondary);
}

.search-item .item-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    color: var(--text-secondary);
}

.search-item:hover .item-icon,
.search-item.active .item-icon {
    background: var(--primary-color);
    color: white;
}

.search-item .item-text {
    flex: 1;
}

.search-item .item-shortcut {
    font-size: var(--text-xs);
    color: var(--text-muted);
    padding: var(--space-1) var(--space-2);
    background: var(--bg-tertiary);
    border-radius: var(--radius-sm);
}

.search-item .item-meta {
    font-size: var(--text-xs);
    color: var(--text-muted);
}

.search-no-results {
    text-align: center;
    padding: var(--space-8);
    color: var(--text-muted);
}

.search-no-results svg {
    margin-bottom: var(--space-2);
}

.search-footer {
    display: flex;
    gap: var(--space-4);
    padding: var(--space-3) var(--space-4);
    border-top: 1px solid var(--border-color);
    background: var(--bg-secondary);
    font-size: var(--text-xs);
    color: var(--text-muted);
}

.search-footer kbd {
    padding: 2px 6px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    margin-right: var(--space-1);
}

@media (max-width: 640px) {
    .search-modal-overlay {
        padding: var(--space-4);
        align-items: flex-start;
    }
    
    .search-modal {
        max-height: 80vh;
    }
    
    .search-footer {
        display: none;
    }
}
</style>

<script>
(function() {
    const modal = document.getElementById('search-modal');
    const input = document.getElementById('search-input');
    const results = document.getElementById('search-results');
    const quickActions = document.getElementById('quick-actions');
    const recentSection = document.getElementById('recent-section');
    const recentItems = document.getElementById('recent-items');
    const pagesSection = document.getElementById('pages-section');
    const keysSection = document.getElementById('keys-section');
    const transactionsSection = document.getElementById('transactions-section');
    const ticketsSection = document.getElementById('tickets-section');
    const noResults = document.getElementById('no-results');
    
    let selectedIndex = -1;
    let searchTimeout = null;
    
    // Open modal with Ctrl+K
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            openSearch();
        }
        
        if (e.key === 'Escape' && modal.style.display !== 'none') {
            closeSearch();
        }
    });
    
    // Expose globally for header button
    window.openSearch = openSearch;
    
    function openSearch() {
        modal.style.display = 'flex';
        input.value = '';
        input.focus();
        resetResults();
        loadRecentSearches();
    }
    
    function closeSearch() {
        modal.style.display = 'none';
        selectedIndex = -1;
    }
    
    // Close on overlay click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeSearch();
        }
    });
    
    // Search input
    input.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (searchTimeout) clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            resetResults();
            return;
        }
        
        searchTimeout = setTimeout(function() {
            performSearch(query);
        }, 200);
    });
    
    // Keyboard navigation
    input.addEventListener('keydown', function(e) {
        const items = results.querySelectorAll('.search-item:not([style*="display: none"])');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            updateSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, 0);
            updateSelection(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0 && items[selectedIndex]) {
                items[selectedIndex].click();
            }
        }
    });
    
    function updateSelection(items) {
        items.forEach((item, i) => {
            item.classList.toggle('active', i === selectedIndex);
        });
        
        if (selectedIndex >= 0 && items[selectedIndex]) {
            items[selectedIndex].scrollIntoView({ block: 'nearest' });
        }
    }
    
    function resetResults() {
        quickActions.style.display = 'block';
        pagesSection.style.display = 'none';
        keysSection.style.display = 'none';
        transactionsSection.style.display = 'none';
        ticketsSection.style.display = 'none';
        noResults.style.display = 'none';
        selectedIndex = -1;
    }
    
    function performSearch(query) {
        fetch('/api/search?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderResults(data.results);
                }
            })
            .catch(err => {
                console.error('Search error:', err);
            });
    }
    
    function renderResults(results) {
        quickActions.style.display = 'none';
        recentSection.style.display = 'none';
        
        let hasResults = false;
        
        // Pages
        if (results.pages && results.pages.length > 0) {
            hasResults = true;
            pagesSection.style.display = 'block';
            document.getElementById('pages-items').innerHTML = results.pages.map(p => `
                <a href="${p.url}" class="search-item">
                    <span class="item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                    </span>
                    <span class="item-text">${escapeHtml(p.name)}</span>
                </a>
            `).join('');
        } else {
            pagesSection.style.display = 'none';
        }
        
        // API Keys
        if (results.api_keys && results.api_keys.length > 0) {
            hasResults = true;
            keysSection.style.display = 'block';
            document.getElementById('keys-items').innerHTML = results.api_keys.map(k => `
                <a href="${k.url}" class="search-item">
                    <span class="item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                        </svg>
                    </span>
                    <span class="item-text">${escapeHtml(k.name)}</span>
                    <span class="item-meta">${k.hint}</span>
                </a>
            `).join('');
        } else {
            keysSection.style.display = 'none';
        }
        
        // Transactions
        if (results.transactions && results.transactions.length > 0) {
            hasResults = true;
            transactionsSection.style.display = 'block';
            document.getElementById('transactions-items').innerHTML = results.transactions.map(t => `
                <a href="${t.url}" class="search-item">
                    <span class="item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </span>
                    <span class="item-text">${t.tx_type}: ${t.amount}</span>
                    <span class="item-meta">${escapeHtml(t.description)}</span>
                </a>
            `).join('');
        } else {
            transactionsSection.style.display = 'none';
        }
        
        // Tickets
        if (results.tickets && results.tickets.length > 0) {
            hasResults = true;
            ticketsSection.style.display = 'block';
            document.getElementById('tickets-items').innerHTML = results.tickets.map(t => `
                <a href="${t.url}" class="search-item">
                    <span class="item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </span>
                    <span class="item-text">${escapeHtml(t.subject)}</span>
                    <span class="item-meta">${t.status}</span>
                </a>
            `).join('');
        } else {
            ticketsSection.style.display = 'none';
        }
        
        noResults.style.display = hasResults ? 'none' : 'block';
    }
    
    function loadRecentSearches() {
        fetch('/api/search/recent')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.recent && data.recent.length > 0) {
                    recentSection.style.display = 'block';
                    recentItems.innerHTML = data.recent.map(r => `
                        <div class="search-item" onclick="document.getElementById('search-input').value='${escapeHtml(r.query)}'; document.getElementById('search-input').dispatchEvent(new Event('input'));">
                            <span class="item-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </span>
                            <span class="item-text">${escapeHtml(r.query)}</span>
                        </div>
                    `).join('');
                } else {
                    recentSection.style.display = 'none';
                }
            })
            .catch(err => {
                recentSection.style.display = 'none';
            });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
</script>
<?php endif; ?>
