<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <span class="page-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                    <path d="M9 16l2 2 4-4"></path>
                </svg>
            </span>
            <?php echo __('checkin.title', 'Diem danh hang ngay'); ?>
        </h1>
        <p class="page-description"><?php echo __('checkin.description', 'Diem danh moi ngay de nhan tokens mien phi!'); ?></p>
    </div>

    <!-- Check-in Stats -->
    <div class="checkin-stats">
        <div class="stat-card streak-card">
            <div class="streak-flames">
                <?php for ($i = 0; $i < min($checkinData['current_streak'], 7); $i++): ?>
                    <span class="flame active">&#128293;</span>
                <?php endfor; ?>
                <?php for ($i = min($checkinData['current_streak'], 7); $i < 7; $i++): ?>
                    <span class="flame">&#128293;</span>
                <?php endfor; ?>
            </div>
            <div class="streak-count"><?php echo $checkinData['current_streak']; ?></div>
            <div class="streak-label"><?php echo __('checkin.current_streak', 'Ngay lien tiep'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $checkinData['max_streak']; ?></div>
            <div class="stat-label"><?php echo __('checkin.max_streak', 'Ky luc streak'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $checkinData['total_checkins']; ?></div>
            <div class="stat-label"><?php echo __('checkin.total_checkins', 'Tong ngay diem danh'); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($checkinData['total_rewards'], 2); ?></div>
            <div class="stat-label"><?php echo __('checkin.total_rewards', 'Tong tokens nhan'); ?></div>
        </div>
    </div>

    <!-- Check-in Button -->
    <div class="checkin-action-container">
        <?php if ($checkinData['today_checked']): ?>
            <div class="checked-in-message">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <h3><?php echo __('checkin.already_checked', 'Ban da diem danh hom nay!'); ?></h3>
                <p><?php echo __('checkin.come_back', 'Quay lai vao ngay mai de tiep tuc streak!'); ?></p>
            </div>
        <?php else: ?>
            <button id="checkinBtn" class="checkin-btn">
                <span class="btn-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <path d="M9 16l2 2 4-4"></path>
                    </svg>
                </span>
                <span class="btn-text"><?php echo __('checkin.checkin_now', 'Diem danh ngay!'); ?></span>
                <span class="btn-reward">+<?php echo number_format($checkinData['next_reward'], 2); ?> tokens</span>
            </button>
            <p class="checkin-hint">
                <?php if ($checkinData['current_streak'] > 0 && ($checkinData['current_streak'] + 1) % 7 === 0): ?>
                    <span class="bonus-hint">&#127881; Ngay mai la ngay thu 7 - x2 bonus!</span>
                <?php else: ?>
                    <?php echo __('checkin.streak_hint', 'Duy tri streak de nhan nhieu hon!'); ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Success Animation (hidden by default) -->
    <div id="checkinSuccess" class="checkin-success" style="display: none;">
        <div class="success-content">
            <div class="confetti-container"></div>
            <div class="success-icon">&#127881;</div>
            <h2><?php echo __('checkin.success', 'Diem danh thanh cong!'); ?></h2>
            <div class="reward-display">
                <span class="reward-label"><?php echo __('checkin.reward_received', 'Ban nhan duoc'); ?></span>
                <span class="reward-amount" id="rewardAmount">+0</span>
                <span class="reward-unit">tokens</span>
            </div>
            <div class="streak-display">
                <span class="streak-label">Streak:</span>
                <span class="streak-value" id="streakValue">0</span>
                <span class="streak-unit"><?php echo __('checkin.days', 'ngay'); ?></span>
            </div>
        </div>
    </div>

    <!-- Calendar Section -->
    <div class="card calendar-card">
        <div class="card-header">
            <button class="nav-btn" onclick="changeMonth(-1)">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <h3 class="calendar-title">
                <?php 
                $monthNames = ['', 'Thang 1', 'Thang 2', 'Thang 3', 'Thang 4', 'Thang 5', 'Thang 6', 'Thang 7', 'Thang 8', 'Thang 9', 'Thang 10', 'Thang 11', 'Thang 12'];
                echo $monthNames[$calendarData['month']] . ', ' . $calendarData['year'];
                ?>
            </h3>
            <button class="nav-btn" onclick="changeMonth(1)">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
        <div class="card-body">
            <div class="calendar">
                <div class="calendar-header">
                    <div class="day-name">CN</div>
                    <div class="day-name">T2</div>
                    <div class="day-name">T3</div>
                    <div class="day-name">T4</div>
                    <div class="day-name">T5</div>
                    <div class="day-name">T6</div>
                    <div class="day-name">T7</div>
                </div>
                <div class="calendar-body">
                    <?php
                    $firstDay = $calendarData['first_day'];
                    $daysInMonth = $calendarData['days_in_month'];
                    $checkins = $calendarData['checkins'];
                    $today = date('Y-m-d');
                    
                    // Empty cells before first day
                    for ($i = 0; $i < $firstDay; $i++) {
                        echo '<div class="calendar-day empty"></div>';
                    }
                    
                    // Days of month
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $date = sprintf('%04d-%02d-%02d', $calendarData['year'], $calendarData['month'], $day);
                        $isCheckedIn = isset($checkins[$date]);
                        $isToday = $date === $today;
                        $isFuture = $date > $today;
                        
                        $classes = ['calendar-day'];
                        if ($isCheckedIn) $classes[] = 'checked';
                        if ($isToday) $classes[] = 'today';
                        if ($isFuture) $classes[] = 'future';
                        
                        echo '<div class="' . implode(' ', $classes) . '">';
                        echo '<span class="day-number">' . $day . '</span>';
                        if ($isCheckedIn) {
                            echo '<span class="check-mark">&#10003;</span>';
                            if ($checkins[$date]['streak'] % 7 === 0) {
                                echo '<span class="streak-badge">' . $checkins[$date]['streak'] . '</span>';
                            }
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Reward Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php echo __('checkin.reward_table', 'Bang phan thuong'); ?></h3>
        </div>
        <div class="card-body">
            <div class="reward-table">
                <?php for ($i = 1; $i <= 7; $i++): 
                    $isCompleted = $checkinData['current_streak'] >= $i;
                    $isCurrent = $checkinData['current_streak'] + 1 === $i;
                    $reward = round(1 * (1 + ($i - 1) * 0.1), 2);
                    if ($i === 7) $reward *= 2;
                ?>
                <div class="reward-item <?php echo $isCompleted ? 'completed' : ''; ?> <?php echo $isCurrent ? 'current' : ''; ?>">
                    <div class="reward-day">
                        <?php echo __('checkin.day', 'Ngay'); ?> <?php echo $i; ?>
                    </div>
                    <div class="reward-value">
                        +<?php echo $reward; ?>
                        <?php if ($i === 7): ?>
                            <span class="bonus-tag">x2</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($isCompleted): ?>
                        <span class="completed-check">&#10003;</span>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<style>
.checkin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    text-align: center;
}

.streak-card {
    background: linear-gradient(135deg, var(--primary-color), #f59e0b);
    color: white;
}

.streak-flames {
    display: flex;
    justify-content: center;
    gap: 0.25rem;
    margin-bottom: 0.5rem;
}

.flame {
    font-size: 1.5rem;
    opacity: 0.3;
}

.flame.active {
    opacity: 1;
    animation: flicker 0.5s ease infinite alternate;
}

@keyframes flicker {
    from { transform: scale(1); }
    to { transform: scale(1.1); }
}

.streak-count {
    font-size: 3rem;
    font-weight: 700;
    line-height: 1;
}

.streak-label, .stat-label {
    font-size: 0.875rem;
    opacity: 0.8;
    margin-top: 0.25rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
}

.checkin-action-container {
    text-align: center;
    margin-bottom: 2rem;
}

.checkin-btn {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem 3rem;
    background: linear-gradient(135deg, var(--primary-color), #10b981);
    border: none;
    border-radius: 16px;
    color: white;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 20px rgba(var(--primary-rgb), 0.3);
}

.checkin-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 30px rgba(var(--primary-rgb), 0.4);
}

.checkin-btn:active {
    transform: scale(0.98);
}

.checkin-btn .btn-icon {
    margin-bottom: 0.5rem;
}

.checkin-btn .btn-text {
    font-size: 1.25rem;
    font-weight: 600;
}

.checkin-btn .btn-reward {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-top: 0.25rem;
}

.checkin-hint {
    margin-top: 1rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.bonus-hint {
    color: #f59e0b;
    font-weight: 500;
}

.checked-in-message {
    padding: 2rem;
    text-align: center;
    color: #10b981;
}

.checked-in-message svg {
    margin-bottom: 1rem;
}

.checked-in-message h3 {
    margin-bottom: 0.5rem;
}

.checked-in-message p {
    color: var(--text-secondary);
}

/* Success Animation */
.checkin-success {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.success-content {
    text-align: center;
    color: white;
    animation: popIn 0.5s ease;
}

@keyframes popIn {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.reward-display {
    font-size: 2rem;
    margin: 1rem 0;
}

.reward-amount {
    color: #10b981;
    font-weight: 700;
    font-size: 3rem;
}

.streak-display {
    font-size: 1.25rem;
    opacity: 0.9;
}

.streak-value {
    color: #f59e0b;
    font-weight: 700;
}

/* Calendar */
.calendar-card .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.nav-btn {
    background: var(--bg-tertiary);
    border: none;
    padding: 0.5rem;
    border-radius: 8px;
    cursor: pointer;
    color: var(--text-primary);
}

.nav-btn:hover {
    background: var(--primary-color);
    color: white;
}

.calendar-title {
    margin: 0;
    font-size: 1.1rem;
}

.calendar {
    margin-top: 1rem;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.day-name {
    text-align: center;
    font-weight: 600;
    font-size: 0.75rem;
    color: var(--text-secondary);
    padding: 0.5rem;
}

.calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: var(--bg-tertiary);
    position: relative;
    font-size: 0.875rem;
}

.calendar-day.empty {
    background: transparent;
}

.calendar-day.today {
    border: 2px solid var(--primary-color);
}

.calendar-day.checked {
    background: linear-gradient(135deg, var(--primary-color), #10b981);
    color: white;
}

.calendar-day.future {
    opacity: 0.5;
}

.check-mark {
    position: absolute;
    top: 2px;
    right: 4px;
    font-size: 0.75rem;
}

.streak-badge {
    position: absolute;
    bottom: 2px;
    font-size: 0.6rem;
    background: #f59e0b;
    padding: 0 4px;
    border-radius: 4px;
}

/* Reward Table */
.reward-table {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
}

.reward-item {
    text-align: center;
    padding: 1rem 0.5rem;
    border-radius: 8px;
    background: var(--bg-tertiary);
    position: relative;
}

.reward-item.completed {
    background: linear-gradient(135deg, var(--primary-color), #10b981);
    color: white;
}

.reward-item.current {
    border: 2px dashed var(--primary-color);
    animation: pulse 2s ease infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.reward-day {
    font-size: 0.75rem;
    margin-bottom: 0.5rem;
    opacity: 0.8;
}

.reward-value {
    font-weight: 700;
    font-size: 0.9rem;
}

.bonus-tag {
    background: #f59e0b;
    color: white;
    font-size: 0.6rem;
    padding: 2px 4px;
    border-radius: 4px;
    margin-left: 2px;
}

.completed-check {
    position: absolute;
    top: 4px;
    right: 4px;
    font-size: 0.75rem;
}

@media (max-width: 576px) {
    .reward-table {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .checkin-btn {
        padding: 1.5rem 2rem;
    }
}
</style>

<script>
function changeMonth(delta) {
    const url = new URL(window.location.href);
    let year = <?php echo $calendarData['year']; ?>;
    let month = <?php echo $calendarData['month']; ?>;
    
    month += delta;
    if (month > 12) { month = 1; year++; }
    if (month < 1) { month = 12; year--; }
    
    url.searchParams.set('year', year);
    url.searchParams.set('month', month);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const checkinBtn = document.getElementById('checkinBtn');
    if (checkinBtn) {
        checkinBtn.addEventListener('click', async function() {
            this.disabled = true;
            this.style.opacity = '0.7';
            
            try {
                const response = await fetch('/checkin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success animation
                    const successDiv = document.getElementById('checkinSuccess');
                    document.getElementById('rewardAmount').textContent = '+' + data.reward;
                    document.getElementById('streakValue').textContent = data.streak;
                    successDiv.style.display = 'flex';
                    
                    // Hide after 3 seconds and reload
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else {
                    alert(data.message);
                    this.disabled = false;
                    this.style.opacity = '1';
                }
            } catch (error) {
                alert('Loi ket noi, vui long thu lai');
                this.disabled = false;
                this.style.opacity = '1';
            }
        });
    }
    
    // Click to close success animation
    const successDiv = document.getElementById('checkinSuccess');
    if (successDiv) {
        successDiv.addEventListener('click', function() {
            location.reload();
        });
    }
});
</script>
