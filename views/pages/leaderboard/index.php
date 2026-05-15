<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                <path d="M4 22h16"></path>
                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
            </svg>
            Bang xep hang
        </h1>
        <p class="page-description">Xem thu hang cua ban so voi nguoi choi khac</p>
    </div>
</div>

<div class="leaderboard-container">
    <!-- Type Tabs -->
    <div class="leaderboard-tabs">
        <button class="tab-btn active" data-type="api_calls">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="4 17 10 11 4 5"></polyline>
                <line x1="12" y1="19" x2="20" y2="19"></line>
            </svg>
            API Calls
        </button>
        <button class="tab-btn" data-type="spending">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            Chi tieu
        </button>
        <button class="tab-btn" data-type="referrals">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Gioi thieu
        </button>
        <button class="tab-btn" data-type="checkin_streak">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Check-in
        </button>
    </div>

    <!-- Period Tabs -->
    <div class="period-tabs">
        <button class="period-btn" data-period="weekly">Tuan nay</button>
        <button class="period-btn" data-period="monthly">Thang nay</button>
        <button class="period-btn active" data-period="all_time">Tat ca</button>
    </div>

    <!-- Your Rank Card -->
    <div class="your-rank-card">
        <div class="rank-card-header">
            <span class="rank-label">Thu hang cua ban</span>
        </div>
        <div class="rank-card-content">
            <div class="your-score">
                <span class="score-value" id="your-score">-</span>
                <span class="score-label" id="score-label">calls</span>
            </div>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="leaderboard-card">
        <div class="leaderboard-header">
            <h3>Top 10</h3>
            <button class="refresh-btn" id="refresh-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
            </button>
        </div>
        
        <div class="leaderboard-content" id="leaderboard-content">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <span>Dang tai...</span>
            </div>
        </div>
    </div>
</div>

<style>
.leaderboard-container {
    max-width: 800px;
    margin: 0 auto;
}

.leaderboard-tabs {
    display: flex;
    gap: var(--space-2);
    margin-bottom: var(--space-4);
    overflow-x: auto;
    padding-bottom: var(--space-2);
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-4);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.tab-btn:hover {
    border-color: var(--primary-color);
    color: var(--text-primary);
}

.tab-btn.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.period-tabs {
    display: flex;
    gap: var(--space-2);
    margin-bottom: var(--space-4);
}

.period-btn {
    padding: var(--space-2) var(--space-3);
    border: none;
    border-radius: var(--radius-md);
    background: transparent;
    color: var(--text-secondary);
    font-size: var(--text-sm);
    cursor: pointer;
    transition: all 0.2s;
}

.period-btn:hover {
    background: var(--bg-secondary);
}

.period-btn.active {
    background: var(--bg-secondary);
    color: var(--primary-color);
    font-weight: 600;
}

.your-rank-card {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    border-radius: var(--radius-xl);
    padding: var(--space-4);
    margin-bottom: var(--space-4);
    color: white;
}

.rank-card-header {
    margin-bottom: var(--space-2);
}

.rank-label {
    font-size: var(--text-sm);
    opacity: 0.9;
}

.rank-card-content {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.your-score .score-value {
    font-size: var(--text-3xl);
    font-weight: 700;
}

.your-score .score-label {
    font-size: var(--text-sm);
    opacity: 0.8;
    margin-left: var(--space-2);
}

.leaderboard-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.leaderboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4);
    border-bottom: 1px solid var(--border-color);
}

.leaderboard-header h3 {
    margin: 0;
    font-size: var(--text-lg);
}

.refresh-btn {
    padding: var(--space-2);
    border: none;
    border-radius: var(--radius-md);
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;
}

.refresh-btn:hover {
    background: var(--bg-secondary);
    color: var(--primary-color);
}

.leaderboard-content {
    min-height: 200px;
}

.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--space-8);
    color: var(--text-secondary);
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid var(--border-color);
    border-top-color: var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: var(--space-2);
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.leaderboard-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.leaderboard-item {
    display: flex;
    align-items: center;
    padding: var(--space-3) var(--space-4);
    border-bottom: 1px solid var(--border-color);
    transition: background 0.2s;
}

.leaderboard-item:last-child {
    border-bottom: none;
}

.leaderboard-item:hover {
    background: var(--bg-secondary);
}

.rank-badge {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 700;
    font-size: var(--text-sm);
    margin-right: var(--space-3);
}

.rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); color: white; }
.rank-2 { background: linear-gradient(135deg, #C0C0C0, #A0A0A0); color: white; }
.rank-3 { background: linear-gradient(135deg, #CD7F32, #A0522D); color: white; }
.rank-other { background: var(--bg-secondary); color: var(--text-secondary); }

.user-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: var(--text-secondary);
}

.user-name {
    font-weight: 500;
}

.user-score {
    font-weight: 600;
    color: var(--primary-color);
}

.empty-state {
    text-align: center;
    padding: var(--space-8);
    color: var(--text-secondary);
}

@media (max-width: 640px) {
    .leaderboard-tabs {
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    
    .tab-btn {
        padding: var(--space-2) var(--space-3);
        font-size: var(--text-sm);
    }
    
    .tab-btn svg {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentType = 'api_calls';
    let currentPeriod = 'all_time';
    
    const scoreLabels = {
        'api_calls': 'calls',
        'spending': 'VND',
        'referrals': 'nguoi',
        'checkin_streak': 'ngay'
    };

    // Tab click handlers
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentType = this.dataset.type;
            loadLeaderboard();
        });
    });

    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentPeriod = this.dataset.period;
            loadLeaderboard();
        });
    });

    document.getElementById('refresh-btn').addEventListener('click', loadLeaderboard);

    function loadLeaderboard() {
        const content = document.getElementById('leaderboard-content');
        content.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><span>Dang tai...</span></div>';
        
        fetch(`/api/leaderboard/${currentType}?period=${currentPeriod}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderLeaderboard(data.leaderboard);
                    updateUserRank(data.user_rank);
                } else {
                    content.innerHTML = '<div class="empty-state">Khong the tai du lieu</div>';
                }
            })
            .catch(err => {
                console.error(err);
                content.innerHTML = '<div class="empty-state">Loi ket noi</div>';
            });
    }

    function renderLeaderboard(leaderboard) {
        const content = document.getElementById('leaderboard-content');
        
        if (!leaderboard || leaderboard.length === 0) {
            content.innerHTML = '<div class="empty-state">Chua co du lieu</div>';
            return;
        }

        let html = '<ul class="leaderboard-list">';
        leaderboard.forEach(item => {
            const rankClass = item.rank <= 3 ? `rank-${item.rank}` : 'rank-other';
            const initial = item.display_name.charAt(0).toUpperCase();
            
            html += `
                <li class="leaderboard-item">
                    <div class="rank-badge ${rankClass}">${item.rank}</div>
                    <div class="user-info">
                        <div class="user-avatar">${initial}</div>
                        <span class="user-name">${item.display_name}</span>
                    </div>
                    <div class="user-score">${item.score}</div>
                </li>
            `;
        });
        html += '</ul>';
        
        content.innerHTML = html;
    }

    function updateUserRank(userRank) {
        document.getElementById('your-score').textContent = userRank.score || '0';
        document.getElementById('score-label').textContent = scoreLabels[currentType] || '';
    }

    // Initial load
    loadLeaderboard();
});
</script>
