<?php
/**
 * Referral Dashboard Page
 * Variables: $pageTitle, $currentPage, $user, $referralCode, $referralLink, $stats, $referrals, $pagination
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1><?php echo __('referral.title', 'Referral Program'); ?></h1>
        <p><?php echo __('referral.subtitle', 'Earn commissions by inviting friends'); ?></p>
    </div>
</div>

<!-- Referral Link Card -->
<div class="card mb-4">
    <div class="card-header">
        <h3><?php echo __('referral.your_link', 'Your Referral Link'); ?></h3>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            <?php echo __('referral.link_description', 'Share this link with your friends. When they sign up and make deposits, you earn'); ?>
            <strong><?php echo number_format($stats['commission_rate'] ?? 5, 0); ?>%</strong>
            <?php echo __('referral.commission_text', 'commission'); ?>.
        </p>
        
        <div class="referral-link-container">
            <div class="referral-link-input">
                <input type="text" id="referralLink" class="form-control" value="<?php echo htmlspecialchars($referralLink); ?>" readonly>
                <button type="button" class="btn btn-primary copy-btn" onclick="copyReferralLink()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <span><?php echo __('referral.copy', 'Copy'); ?></span>
                </button>
            </div>
        </div>
        
        <div class="referral-code-display mt-3">
            <span class="text-muted"><?php echo __('referral.your_code', 'Your Code'); ?>:</span>
            <span class="referral-code"><?php echo htmlspecialchars($referralCode); ?></span>
            <form action="/referral/generate" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <button type="submit" class="btn btn-sm btn-link" onclick="return confirm('<?php echo __('referral.regenerate_confirm', 'Are you sure you want to regenerate your referral code? Your old link will no longer work.'); ?>')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                    </svg>
                    <?php echo __('referral.regenerate', 'Regenerate'); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid stats-grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?php echo number_format($stats['total_referrals'] ?? 0); ?></span>
            <span class="stat-label"><?php echo __('referral.total_referrals', 'Total Referrals'); ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?php echo number_format($stats['pending'] ?? 0); ?></span>
            <span class="stat-label"><?php echo __('referral.pending', 'Pending'); ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?php echo number_format($stats['approved'] ?? 0); ?></span>
            <span class="stat-label"><?php echo __('referral.approved', 'Approved'); ?></span>
        </div>
    </div>

    <div class="stat-card stat-card-highlight">
        <div class="stat-icon stat-icon-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?php echo number_format($stats['total_earned'] ?? 0, 2); ?></span>
            <span class="stat-label"><?php echo __('referral.total_earned', 'Total Earned'); ?></span>
        </div>
    </div>
</div>

<!-- Withdraw Section -->
<?php if (($stats['available_to_withdraw'] ?? 0) > 0): ?>
<div class="card mb-4 withdraw-card">
    <div class="card-body">
        <div class="withdraw-content">
            <div class="withdraw-info">
                <h4><?php echo __('referral.available_withdraw', 'Available to Withdraw'); ?></h4>
                <span class="withdraw-amount">$<?php echo number_format($stats['available_to_withdraw'], 2); ?></span>
            </div>
            <form action="/referral/withdraw" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('<?php echo __('referral.withdraw_confirm', 'Transfer earnings to your main balance?'); ?>')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <?php echo __('referral.withdraw', 'Withdraw to Balance'); ?>
                </button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Referrals List -->
<div class="card">
    <div class="card-header">
        <h3><?php echo __('referral.your_referrals', 'Your Referrals'); ?></h3>
    </div>
    <div class="card-body">
        <?php if (!empty($referrals)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo __('referral.user', 'User'); ?></th>
                        <th><?php echo __('referral.date', 'Date'); ?></th>
                        <th><?php echo __('referral.status', 'Status'); ?></th>
                        <th><?php echo __('referral.commission', 'Commission'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrals as $referral): ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <span class="user-name"><?php echo htmlspecialchars($referral['referred_name'] ?? 'Unknown'); ?></span>
                                <span class="user-email text-muted"><?php echo htmlspecialchars($referral['referred_email'] ?? ''); ?></span>
                            </div>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($referral['created_at'] ?? '')); ?></td>
                        <td>
                            <?php
                            $statusClass = 'warning';
                            $statusText = __('referral.status_pending', 'Pending');
                            if ($referral['status'] === 'approved') {
                                $statusClass = 'success';
                                $statusText = __('referral.status_approved', 'Approved');
                            } elseif ($referral['status'] === 'paid') {
                                $statusClass = 'info';
                                $statusText = __('referral.status_paid', 'Paid');
                            }
                            ?>
                            <span class="badge badge-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        </td>
                        <td class="commission-amount">
                            $<?php echo number_format($referral['commission_earned'] ?? 0, 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination-wrapper">
            <nav class="pagination">
                <?php if ($pagination['page'] > 1): ?>
                <a href="?page=<?php echo $pagination['page'] - 1; ?>" class="pagination-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
                <?php endif; ?>
                
                <span class="pagination-info">
                    <?php echo __('pagination.page', 'Page'); ?> <?php echo $pagination['page']; ?> <?php echo __('pagination.of', 'of'); ?> <?php echo $pagination['total_pages']; ?>
                </span>
                
                <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                <a href="?page=<?php echo $pagination['page'] + 1; ?>" class="pagination-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <h4><?php echo __('referral.no_referrals', 'No referrals yet'); ?></h4>
            <p class="text-muted"><?php echo __('referral.no_referrals_desc', 'Share your referral link to start earning commissions'); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- How It Works -->
<div class="card mt-4">
    <div class="card-header">
        <h3><?php echo __('referral.how_it_works', 'How It Works'); ?></h3>
    </div>
    <div class="card-body">
        <div class="how-it-works">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4><?php echo __('referral.step1_title', 'Share Your Link'); ?></h4>
                    <p><?php echo __('referral.step1_desc', 'Copy your unique referral link and share it with friends, on social media, or your website.'); ?></p>
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4><?php echo __('referral.step2_title', 'Friends Sign Up'); ?></h4>
                    <p><?php echo __('referral.step2_desc', 'When someone uses your link to create an account, they become your referral.'); ?></p>
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4><?php echo __('referral.step3_title', 'Earn Commission'); ?></h4>
                    <p><?php echo __('referral.step3_desc', 'You earn a percentage of every deposit your referrals make. Commission is added to your balance automatically.'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    const linkInput = document.getElementById('referralLink');
    linkInput.select();
    linkInput.setSelectionRange(0, 99999);
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(linkInput.value).then(function() {
            showCopySuccess();
        }).catch(function() {
            document.execCommand('copy');
            showCopySuccess();
        });
    } else {
        document.execCommand('copy');
        showCopySuccess();
    }
}

function showCopySuccess() {
    const btn = document.querySelector('.copy-btn span');
    const originalText = btn.textContent;
    btn.textContent = '<?php echo __("referral.copied", "Copied!"); ?>';
    setTimeout(function() {
        btn.textContent = originalText;
    }, 2000);
}
</script>
