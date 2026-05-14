<?php /** Landing Page */ ?>
<div class="landing-page">
<!-- Navigation -->
<nav class="landing-nav">
<div class="nav-container">
<a href="/" class="nav-logo">
<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"/><path d="m21 2-9.6 9.6"/><circle cx="7.5" cy="15.5" r="5.5"/></svg>
<span>Hay API</span>
</a>
<div class="nav-links">
<a href="#features">Features</a>
<a href="#pricing">Pricing</a>
<a href="#faq">FAQ</a>
<a href="/docs">Docs</a>
</div>
<div class="nav-actions">
<a href="/login" class="btn btn-ghost">Sign In</a>
<a href="/register" class="btn btn-primary">Get Started</a>
</div>
<button class="mobile-menu-toggle" aria-label="Toggle menu">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
</button>
</div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
<div class="hero-container">
<div class="hero-badge">Powerful AI API Gateway</div>
<h1 class="hero-title">Simplify Your <span class="gradient-text">AI Integration</span></h1>
<p class="hero-subtitle">One API to access multiple AI providers. Intelligent routing, rate limiting, usage analytics, and cost optimization built-in.</p>
<div class="hero-cta">
<a href="/register" class="btn btn-primary btn-lg">Start Free Trial</a>
<a href="/docs" class="btn btn-outline btn-lg">View Documentation</a>
</div>
<div class="hero-stats">
<div class="stat"><span class="stat-value">99.9%</span><span class="stat-label">Uptime</span></div>
<div class="stat"><span class="stat-value">50ms</span><span class="stat-label">Avg Latency</span></div>
<div class="stat"><span class="stat-value">10M+</span><span class="stat-label">API Calls/Day</span></div>
</div>
</div>
</section>

<!-- Features Section -->
<section class="features-section" id="features">
<div class="section-container">
<div class="section-header">
<h2 class="section-title">Everything You Need</h2>
<p class="section-subtitle">Powerful features to manage your AI API usage efficiently</p>
</div>
<div class="features-grid">
<div class="feature-card">
<div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg></div>
<h3>Secure API Keys</h3>
<p>Generate and manage multiple API keys with granular permissions and automatic rotation.</p>
</div>
<div class="feature-card">
<div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg></div>
<h3>Usage Analytics</h3>
<p>Real-time dashboards showing token usage, costs, and performance metrics across all your projects.</p>
</div>
<div class="feature-card">
<div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
<h3>Rate Limiting</h3>
<p>Smart rate limiting per key, per user, or per model to prevent abuse and manage costs.</p>
</div>
<div class="feature-card">
<div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/></svg></div>
<h3>Model Routing</h3>
<p>Intelligently route requests to the best AI model based on cost, speed, or capability.</p>
</div>
<div class="feature-card">
<div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
<h3>Cost Management</h3>
<p>Set budgets, get alerts, and optimize spending across multiple AI providers.</p>
</div>
<div class="feature-card">
<div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
<h3>Enterprise Security</h3>
<p>SOC 2 compliant with encrypted keys, audit logs, and SSO integration.</p>
</div>
</div>
</div>
</section>

<!-- Pricing Section -->
<section class="pricing-section" id="pricing">
<div class="section-container">
<div class="section-header">
<h2 class="section-title">Simple, Transparent Pricing</h2>
<p class="section-subtitle">Choose the plan that fits your needs. Start free, scale as you grow.</p>
</div>
<div class="pricing-grid">
<?php if (!empty($plans)): ?>
<?php foreach ($plans as $index => $plan): ?>
<div class="pricing-card <?php echo $index === 1 ? 'featured' : ''; ?>">
<?php if ($index === 1): ?><div class="pricing-badge">Most Popular</div><?php endif; ?>
<h3 class="pricing-name"><?php echo htmlspecialchars($plan['name']); ?></h3>
<div class="pricing-price">
<span class="price-amount">$<?php echo number_format($plan['price_monthly'] ?? 0, 0); ?></span>
<span class="price-period">/month</span>
</div>
<p class="pricing-desc"><?php echo htmlspecialchars($plan['description'] ?? 'Perfect for getting started'); ?></p>
<ul class="pricing-features">
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><?php echo number_format($plan['token_quota'] ?? 0); ?> tokens/month</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><?php echo $plan['rate_limit_per_minute'] ?? 60; ?> requests/minute</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Usage Analytics</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Email Support</li>
</ul>
<a href="/register" class="btn <?php echo $index === 1 ? 'btn-primary' : 'btn-outline'; ?> w-full">Get Started</a>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="pricing-card">
<h3 class="pricing-name">Free</h3>
<div class="pricing-price"><span class="price-amount">$0</span><span class="price-period">/month</span></div>
<p class="pricing-desc">Perfect for trying out</p>
<ul class="pricing-features">
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>10,000 tokens/month</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>10 requests/minute</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Basic Analytics</li>
</ul>
<a href="/register" class="btn btn-outline w-full">Get Started</a>
</div>
<div class="pricing-card featured">
<div class="pricing-badge">Most Popular</div>
<h3 class="pricing-name">Pro</h3>
<div class="pricing-price"><span class="price-amount">$29</span><span class="price-period">/month</span></div>
<p class="pricing-desc">For growing projects</p>
<ul class="pricing-features">
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>500,000 tokens/month</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>100 requests/minute</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Advanced Analytics</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Priority Support</li>
</ul>
<a href="/register" class="btn btn-primary w-full">Get Started</a>
</div>
<div class="pricing-card">
<h3 class="pricing-name">Enterprise</h3>
<div class="pricing-price"><span class="price-amount">$99</span><span class="price-period">/month</span></div>
<p class="pricing-desc">For large teams</p>
<ul class="pricing-features">
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Unlimited tokens</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Unlimited requests</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Custom Integrations</li>
<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Dedicated Support</li>
</ul>
<a href="/register" class="btn btn-outline w-full">Contact Sales</a>
</div>
<?php endif; ?>
</div>
</div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section">
<div class="section-container">
<div class="section-header">
<h2 class="section-title">Loved by Developers</h2>
<p class="section-subtitle">See what our customers are saying</p>
</div>
<div class="testimonials-grid">
<div class="testimonial-card">
<p class="testimonial-text">"Hay API simplified our AI integration. We went from managing 5 different API keys to just one. The analytics are incredible."</p>
<div class="testimonial-author">
<div class="author-avatar">JD</div>
<div class="author-info"><div class="author-name">John Doe</div><div class="author-title">CTO at TechStartup</div></div>
</div>
</div>
<div class="testimonial-card">
<p class="testimonial-text">"The rate limiting and cost management features saved us thousands. Best decision we made for our AI infrastructure."</p>
<div class="testimonial-author">
<div class="author-avatar">SJ</div>
<div class="author-info"><div class="author-name">Sarah Johnson</div><div class="author-title">Lead Developer at AILabs</div></div>
</div>
</div>
<div class="testimonial-card">
<p class="testimonial-text">"Setup took 5 minutes. The documentation is excellent and the support team is incredibly responsive."</p>
<div class="testimonial-author">
<div class="author-avatar">MK</div>
<div class="author-info"><div class="author-name">Mike Kim</div><div class="author-title">Founder at DevTools Inc</div></div>
</div>
</div>
</div>
</div>
</section>

<!-- FAQ Section -->
<section class="faq-section" id="faq">
<div class="section-container">
<div class="section-header">
<h2 class="section-title">Frequently Asked Questions</h2>
<p class="section-subtitle">Got questions? We have answers.</p>
</div>
<div class="faq-list">
<div class="faq-item">
<button class="faq-question" aria-expanded="false">
<span>How does Hay API Gateway work?</span>
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
</button>
<div class="faq-answer"><p>Hay API acts as a proxy between your application and AI providers. You make requests to our endpoint with your Hay API key, and we route them to the appropriate provider while handling authentication, rate limiting, and usage tracking.</p></div>
</div>
<div class="faq-item">
<button class="faq-question" aria-expanded="false">
<span>Which AI providers do you support?</span>
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
</button>
<div class="faq-answer"><p>We support all major AI providers including OpenAI, Anthropic, Google, and more. Our API is OpenAI-compatible, making it easy to switch providers without changing your code.</p></div>
</div>
<div class="faq-item">
<button class="faq-question" aria-expanded="false">
<span>Is my data secure?</span>
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
</button>
<div class="faq-answer"><p>Yes! We take security seriously. All data is encrypted in transit and at rest. We do not store your prompts or responses. Your API keys are hashed and never stored in plain text.</p></div>
</div>
<div class="faq-item">
<button class="faq-question" aria-expanded="false">
<span>Can I cancel my subscription anytime?</span>
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
</button>
<div class="faq-answer"><p>Absolutely! You can upgrade, downgrade, or cancel your subscription at any time. No long-term contracts or hidden fees.</p></div>
</div>
<div class="faq-item">
<button class="faq-question" aria-expanded="false">
<span>Do you offer a free trial?</span>
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
</button>
<div class="faq-answer"><p>Yes! Our Free plan is available forever with 10,000 tokens per month. No credit card required to get started.</p></div>
</div>
</div>
</div>
</section>

<!-- CTA Section -->
<section class="cta-section">
<div class="section-container">
<div class="cta-content">
<h2>Ready to Get Started?</h2>
<p>Join thousands of developers using Hay API to power their AI applications.</p>
<div class="cta-buttons">
<a href="/register" class="btn btn-white btn-lg">Start Free Trial</a>
<a href="/docs" class="btn btn-ghost-white btn-lg">Read the Docs</a>
</div>
</div>
</div>
</section>

<!-- Footer -->
<footer class="landing-footer">
<div class="footer-container">
<div class="footer-grid">
<div class="footer-brand">
<a href="/" class="footer-logo">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"/><path d="m21 2-9.6 9.6"/><circle cx="7.5" cy="15.5" r="5.5"/></svg>
<span>Hay API</span>
</a>
<p>Simplify your AI integration with our powerful API gateway.</p>
</div>
<div class="footer-links">
<h4>Product</h4>
<a href="#features">Features</a>
<a href="#pricing">Pricing</a>
<a href="/docs">Documentation</a>
<a href="/changelog">Changelog</a>
</div>
<div class="footer-links">
<h4>Company</h4>
<a href="/page/about">About</a>
<a href="/page/privacy">Privacy</a>
<a href="/page/terms">Terms</a>
</div>
<div class="footer-links">
<h4>Support</h4>
<a href="/docs">Help Center</a>
<a href="/tickets">Contact</a>
</div>
</div>
<div class="footer-bottom">
<p>&copy; <?php echo date('Y'); ?> Hay API Gateway. All rights reserved.</p>
</div>
</div>
</footer>

<!-- Mobile Menu -->
<div class="mobile-menu">
<div class="mobile-menu-content">
<a href="#features">Features</a>
<a href="#pricing">Pricing</a>
<a href="#faq">FAQ</a>
<a href="/docs">Docs</a>
<hr>
<a href="/login" class="btn btn-ghost w-full">Sign In</a>
<a href="/register" class="btn btn-primary w-full">Get Started</a>
</div>
</div>
</div>
