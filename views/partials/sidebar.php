<aside class="sidebar hide-mobile" id="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav-menu">
            <!-- User Section -->
            <li class="nav-section"><?php echo __('nav.main', 'Main'); ?></li>
            
            <li class="nav-item">
                <a href="/dashboard" class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.dashboard', 'Dashboard'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/keys" class="nav-link <?php echo ($currentPage === 'keys') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.api_keys', 'API Keys'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/playground" class="nav-link <?php echo ($currentPage === 'playground') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="4 17 10 11 4 5"></polyline>
                            <line x1="12" y1="19" x2="20" y2="19"></line>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.playground', 'API Playground'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/billing" class="nav-link <?php echo (strpos($currentPage, 'billing') === 0 && $currentPage !== 'billing-auto-topup' || $currentPage === 'deposit') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.billing', 'Billing'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/referral" class="nav-link <?php echo ($currentPage === 'referral') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.referral', 'Referrals'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/webhooks" class="nav-link <?php echo ($currentPage === 'webhooks') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.webhooks', 'Webhooks'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/analytics" class="nav-link <?php echo ($currentPage === 'analytics') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.analytics', 'Analytics'); ?></span>
                </a>
            </li>
            
            <!-- Rewards Section -->
            <li class="nav-section"><?php echo __('nav.rewards', 'Rewards'); ?></li>
            
            <li class="nav-item">
                <a href="/giftcode" class="nav-link <?php echo ($currentPage === 'giftcode') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 12 20 22 4 22 4 12"></polyline>
                            <rect x="2" y="7" width="20" height="5"></rect>
                            <line x1="12" y1="22" x2="12" y2="7"></line>
                            <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.giftcode', 'Gift Code'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/checkin" class="nav-link <?php echo ($currentPage === 'checkin') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                            <path d="M9 16l2 2 4-4"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.checkin', 'Check-in'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/achievements" class="nav-link <?php echo ($currentPage === 'achievements') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="7"></circle>
                            <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.achievements', 'Achievements'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/favorites" class="nav-link <?php echo ($currentPage === 'favorites') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.favorites', 'Favorites'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/leaderboard" class="nav-link <?php echo ($currentPage === 'leaderboard') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                            <path d="M4 22h16"></path>
                            <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                            <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.leaderboard', 'Leaderboard'); ?></span>
                </a>
            </li>
            
            <!-- Security Section -->
            <li class="nav-section"><?php echo __('nav.security', 'Security'); ?></li>
            
            <li class="nav-item">
                <a href="/security/login-history" class="nav-link <?php echo ($currentPage === 'security-login-history') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.login_history', 'Login History'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/security/sessions" class="nav-link <?php echo ($currentPage === 'security-sessions') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.sessions', 'Sessions'); ?></span>
                </a>
            </li>
            
            <!-- Resources Section -->
            <li class="nav-section"><?php echo __('nav.resources', 'Resources'); ?></li>
            
            <li class="nav-item">
                <a href="/changelog" class="nav-link <?php echo ($currentPage === 'changelog') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.changelog', 'Changelog'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/docs" class="nav-link <?php echo ($currentPage === 'docs') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.documentation', 'Documentation'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/tickets" class="nav-link <?php echo (strpos($currentPage, 'ticket') === 0) ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.support', 'Support'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/feedback" class="nav-link <?php echo ($currentPage === 'feedback') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.feedback', 'Feedback'); ?></span>
                </a>
            </li>
            
            <?php if (isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin']): ?>
            <!-- Admin Section -->
            <li class="nav-section"><?php echo __('nav.admin', 'Admin'); ?></li>
            
            <li class="nav-item">
                <a href="/admin" class="nav-link <?php echo ($currentPage === 'admin' || $currentPage === 'admin-dashboard') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.admin_dashboard', 'Admin Dashboard'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/users" class="nav-link <?php echo ($currentPage === 'admin-users') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.users', 'Users'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/deposits" class="nav-link <?php echo ($currentPage === 'admin-deposits') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.deposits', 'Deposits'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/tickets" class="nav-link <?php echo ($currentPage === 'admin-tickets') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.tickets', 'Tickets'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/plans" class="nav-link <?php echo ($currentPage === 'admin-plans') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.plans', 'Plans'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/providers" class="nav-link <?php echo ($currentPage === 'admin-providers') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                            <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                            <line x1="6" y1="6" x2="6.01" y2="6"></line>
                            <line x1="6" y1="18" x2="6.01" y2="18"></line>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.providers', 'Providers'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/model-pricing" class="nav-link <?php echo ($currentPage === 'admin-model-pricing') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.model_pricing', 'Model Pricing'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/logs" class="nav-link <?php echo ($currentPage === 'admin-logs') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <line x1="10" y1="9" x2="8" y2="9"></line>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.audit_logs', 'Audit Logs'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/system-settings" class="nav-link <?php echo ($currentPage === 'admin-settings') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.settings', 'Settings'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/changelogs" class="nav-link <?php echo ($currentPage === 'admin-changelogs') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.changelogs', 'Changelogs'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/giftcodes" class="nav-link <?php echo ($currentPage === 'admin-giftcodes') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 12 20 22 4 22 4 12"></polyline>
                            <rect x="2" y="7" width="20" height="5"></rect>
                            <line x1="12" y1="22" x2="12" y2="7"></line>
                            <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.giftcodes', 'Gift Codes'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/roles" class="nav-link <?php echo ($currentPage === 'admin-roles') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.roles', 'Roles'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/health" class="nav-link <?php echo ($currentPage === 'admin-health') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.health', 'System Health'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/maintenance" class="nav-link <?php echo ($currentPage === 'admin-maintenance') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.maintenance', 'Maintenance'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/chat" class="nav-link <?php echo ($currentPage === 'admin-chat') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.live_chat', 'Live Chat'); ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/feedback" class="nav-link <?php echo ($currentPage === 'admin-feedback') ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </span>
                    <span class="nav-text"><?php echo __('nav.feedback', 'Feedback'); ?></span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
