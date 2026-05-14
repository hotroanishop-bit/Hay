<?php
/**
 * Billing Plans Page - Available Subscription Plans
 * Variables: $pageTitle, $currentPage, $balances, $currentPlan, $availablePlans
 */

// Helper function to format token numbers
function formatTokensDisplay($tokens) {
    if ($tokens >= 1000000) {
        return number_format($tokens / 1000000, 1) . 'M';
    } elseif ($tokens >= 1000) {
        return number_format($tokens / 1000, 1) . 'K';
    }
    return number_format($tokens);
}

// Helper function to format price
function formatPrice($price) {
    if ($price == 0) {
        return 'Free';
    }
    return number_format($price) . ' VND';
}
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Subscription Plans</h1>
        <p>Choose the plan that best fits your needs</p>
    </div>
    <div class="page-header-actions">
        <a href="/billing" class="btn btn-secondary">
            <i class="icon-arrow-left"></i> Back to Billing
        </a>
    </div>
</div>

<!-- Current Plan Info -->
<?php if ($currentPlan): ?>
<div class="alert alert-info mb-4">
    <i class="icon-info"></i>
    <strong>Current Plan:</strong> <?= htmlspecialchars($currentPlan['name'] ?? 'Unknown') ?>
    with <?= formatTokensDisplay($balances['plan_tokens'] ?? 0) ?> tokens remaining.
    <?php if (!empty($balances['plan_tokens_expires_at'])): ?>
    Expires on <?= date('M d, Y', strtotime($balances['plan_tokens_expires_at'])) ?>.
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Plans Grid -->
<div class="plans-grid">
    <?php if (!empty($availablePlans)): ?>
    <?php foreach ($availablePlans as $plan): ?>
    <?php 
        $isCurrentPlan = $currentPlan && $currentPlan['id'] == $plan['id'];
        $isFree = !empty($plan['is_free']);
    ?>
    <div class="plan-card <?= $isCurrentPlan ? 'plan-card-current' : '' ?> <?= $isFree ? 'plan-card-free' : '' ?>">
        <?php if ($isCurrentPlan): ?>
        <div class="plan-badge">Current Plan</div>
        <?php elseif ($isFree): ?>
        <div class="plan-badge plan-badge-free">Free</div>
        <?php endif; ?>
        
        <div class="plan-card-header">
            <h3 class="plan-name"><?= htmlspecialchars($plan['name'] ?? 'Unknown Plan') ?></h3>
            <div class="plan-price">
                <?php if ($isFree): ?>
                <span class="price-amount">Free</span>
                <?php else: ?>
                <span class="price-amount"><?= number_format($plan['price_monthly'] ?? 0) ?></span>
                <span class="price-currency">VND</span>
                <span class="price-period">/month</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="plan-card-body">
            <?php if (!empty($plan['description'])): ?>
            <p class="plan-description"><?= htmlspecialchars($plan['description']) ?></p>
            <?php endif; ?>
            
            <ul class="plan-features">
                <li>
                    <i class="icon-check"></i>
                    <strong><?= formatTokensDisplay($plan['token_quota'] ?? 0) ?></strong> tokens per period
                </li>
                <?php if (!empty($plan['duration_days'])): ?>
                <li>
                    <i class="icon-check"></i>
                    <strong><?= (int)$plan['duration_days'] ?></strong> days validity
                </li>
                <?php else: ?>
                <li>
                    <i class="icon-check"></i>
                    <strong>Unlimited</strong> validity
                </li>
                <?php endif; ?>
                <?php if (!empty($plan['rate_limit_per_minute'])): ?>
                <li>
                    <i class="icon-check"></i>
                    <strong><?= number_format($plan['rate_limit_per_minute']) ?></strong> requests/min
                </li>
                <?php endif; ?>
                <?php if (!empty($plan['daily_token_limit'])): ?>
                <li>
                    <i class="icon-check"></i>
                    <strong><?= formatTokensDisplay($plan['daily_token_limit']) ?></strong> daily token limit
                </li>
                <?php endif; ?>
                <?php if (!empty($plan['price_multiplier']) && $plan['price_multiplier'] < 1): ?>
                <li>
                    <i class="icon-check"></i>
                    <strong><?= round((1 - $plan['price_multiplier']) * 100) ?>%</strong> discount on PAYG
                </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="plan-card-footer">
            <?php if ($isCurrentPlan): ?>
            <button class="btn btn-secondary btn-block" disabled>
                <i class="icon-check"></i> Current Plan
            </button>
            <?php elseif ($isFree): ?>
            <form action="/billing/subscribe/<?= (int)$plan['id'] ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button type="submit" class="btn btn-success btn-block">
                    <i class="icon-gift"></i> Activate Free Plan
                </button>
            </form>
            <?php else: ?>
            <form action="/billing/subscribe/<?= (int)$plan['id'] ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button type="submit" class="btn btn-primary btn-block" onclick="return confirm('Subscribe to <?= htmlspecialchars($plan['name'] ?? 'this plan') ?> for <?= number_format($plan['price_monthly'] ?? 0) ?> VND?')">
                    <i class="icon-credit-card"></i> Subscribe Now
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="empty-state">
        <i class="icon-package"></i>
        <h3>No Plans Available</h3>
        <p>There are no subscription plans available at the moment.</p>
    </div>
    <?php endif; ?>
</div>

<!-- FAQ Section -->
<div class="card mt-5">
    <div class="card-header">
        <h3>Frequently Asked Questions</h3>
    </div>
    <div class="card-body">
        <div class="faq-item">
            <h4>What happens when my plan tokens run out?</h4>
            <p>When your plan tokens are exhausted, API requests will automatically fall back to your PAYG (Pay-As-You-Go) balance if available. You can also manually switch your billing preference.</p>
        </div>
        
        <div class="faq-item">
            <h4>Can I switch between PAYG and Plan billing?</h4>
            <p>Yes! You can set your preferred billing method in the Billing page. You can also specify the billing type per request using the <code>X-Billing-Type</code> header with values <code>payg</code> or <code>plan</code>.</p>
        </div>
        
        <div class="faq-item">
            <h4>What happens to unused tokens when my plan expires?</h4>
            <p>Unused tokens expire when your plan period ends. We recommend using tokens before the expiration date. Consider upgrading or renewing your plan to continue using the service.</p>
        </div>
        
        <div class="faq-item">
            <h4>Can I cancel my subscription?</h4>
            <p>Yes, you can cancel your subscription at any time from the Billing page. Note that remaining tokens will be forfeited upon cancellation.</p>
        </div>
        
        <div class="faq-item">
            <h4>How do daily token limits work?</h4>
            <p>Some plans (especially free plans) have daily token limits that reset at midnight UTC. This ensures fair usage across all users.</p>
        </div>
    </div>
</div>

<style>
.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.plan-card {
    position: relative;
    background: var(--bg-primary, #ffffff);
    border: 2px solid var(--border-color, #e9ecef);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s;
}

.plan-card:hover {
    border-color: var(--primary-color, #007bff);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-4px);
}

.plan-card-current {
    border-color: var(--success-color, #28a745);
}

.plan-card-free {
    border-color: var(--info-color, #17a2b8);
}

.plan-badge {
    position: absolute;
    top: 12px;
    right: -30px;
    background: var(--success-color, #28a745);
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 40px;
    transform: rotate(45deg);
}

.plan-badge-free {
    background: var(--info-color, #17a2b8);
}

.plan-card-header {
    padding: 1.5rem 1.5rem 1rem;
    text-align: center;
    border-bottom: 1px solid var(--border-color, #e9ecef);
}

.plan-name {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
    color: var(--text-primary, #212529);
}

.plan-price {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0.25rem;
}

.price-amount {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color, #007bff);
}

.price-currency {
    font-size: 1rem;
    color: var(--text-muted, #6c757d);
}

.price-period {
    font-size: 0.875rem;
    color: var(--text-muted, #6c757d);
}

.plan-card-body {
    padding: 1.5rem;
}

.plan-description {
    color: var(--text-muted, #6c757d);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.plan-features li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-light, #f1f3f4);
}

.plan-features li:last-child {
    border-bottom: none;
}

.plan-features i {
    color: var(--success-color, #28a745);
    font-size: 1rem;
}

.plan-card-footer {
    padding: 1rem 1.5rem 1.5rem;
}

.faq-item {
    padding: 1rem 0;
    border-bottom: 1px solid var(--border-color, #e9ecef);
}

.faq-item:last-child {
    border-bottom: none;
}

.faq-item h4 {
    font-size: 1rem;
    margin-bottom: 0.5rem;
    color: var(--text-primary, #212529);
}

.faq-item p {
    margin: 0;
    color: var(--text-muted, #6c757d);
    font-size: 0.9rem;
}

.faq-item code {
    background: var(--bg-secondary, #f8f9fa);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.85em;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
    color: var(--text-muted, #6c757d);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h3 {
    margin-bottom: 0.5rem;
}
</style>
