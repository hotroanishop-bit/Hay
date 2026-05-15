<?php
/**
 * Web Routes
 * Defines all application routes with their controllers and middleware
 *
 * Route structure:
 * [
 *     'method' => 'GET' or 'POST',
 *     'path' => '/path/{parameter}',
 *     'controller' => ControllerClass::class,
 *     'action' => 'methodName',
 *     'middleware' => ['MiddlewareClass', ...] (optional)
 * ]
 */

$routes = [
    // =====================
    // Landing Page (Public)
    // =====================
    [
        'method' => 'GET',
        'path' => '/',
        'controller' => LandingController::class,
        'action' => 'index',
        'middleware' => []
    ],

    // =====================
    // API Documentation (Public)
    // =====================
    [
        'method' => 'GET',
        'path' => '/docs',
        'controller' => DocsController::class,
        'action' => 'index',
        'middleware' => []
    ],

    // =====================
    // Auth Routes (Public)
    // =====================
    [
        'method' => 'GET',
        'path' => '/login',
        'controller' => AuthController::class,
        'action' => 'showLogin',
        'middleware' => []
    ],
    [
        'method' => 'POST',
        'path' => '/login',
        'controller' => AuthController::class,
        'action' => 'login',
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/register',
        'controller' => AuthController::class,
        'action' => 'showRegister',
        'middleware' => []
    ],
    [
        'method' => 'POST',
        'path' => '/register',
        'controller' => AuthController::class,
        'action' => 'register',
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/2fa',
        'controller' => AuthController::class,
        'action' => 'show2FA',
        'middleware' => []
    ],
    [
        'method' => 'POST',
        'path' => '/2fa',
        'controller' => AuthController::class,
        'action' => 'verify2FA',
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/logout',
        'controller' => AuthController::class,
        'action' => 'logout',
        'middleware' => []
    ],
    [
        'method' => 'POST',
        'path' => '/logout',
        'controller' => AuthController::class,
        'action' => 'logout',
        'middleware' => []
    ],

    // =====================
    // Password Reset Routes (Public)
    // =====================
    [
        'method' => 'GET',
        'path' => '/forgot-password',
        'controller' => AuthController::class,
        'action' => 'showForgotPassword',
        'middleware' => []
    ],
    [
        'method' => 'POST',
        'path' => '/forgot-password',
        'controller' => AuthController::class,
        'action' => 'forgotPassword',
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/reset-password',
        'controller' => AuthController::class,
        'action' => 'showResetPassword',
        'middleware' => []
    ],
    [
        'method' => 'POST',
        'path' => '/reset-password',
        'controller' => AuthController::class,
        'action' => 'resetPassword',
        'middleware' => []
    ],

    // =====================
    // Email Verification Routes (Public)
    // =====================
    [
        'method' => 'GET',
        'path' => '/verify-email',
        'controller' => AuthController::class,
        'action' => 'showVerifyEmail',
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/verify-email/confirm',
        'controller' => AuthController::class,
        'action' => 'verifyEmail',
        'middleware' => []
    ],
    [
        'method' => 'POST',
        'path' => '/resend-verification',
        'controller' => AuthController::class,
        'action' => 'resendVerification',
        'middleware' => []
    ],

    // =====================
    // Dashboard Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/dashboard',
        'controller' => DashboardController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Profile Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/profile',
        'controller' => ProfileController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/profile',
        'controller' => ProfileController::class,
        'action' => 'update',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/profile/password',
        'controller' => ProfileController::class,
        'action' => 'showPassword',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/profile/password',
        'controller' => ProfileController::class,
        'action' => 'updatePassword',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/profile/avatar',
        'controller' => ProfileController::class,
        'action' => 'uploadAvatar',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/profile/2fa',
        'controller' => ProfileController::class,
        'action' => 'show2FA',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/profile/2fa/enable',
        'controller' => ProfileController::class,
        'action' => 'enable2FA',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/profile/2fa/disable',
        'controller' => ProfileController::class,
        'action' => 'disable2FA',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/profile/delete',
        'controller' => ProfileController::class,
        'action' => 'deleteAccount',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // API Keys Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/keys',
        'controller' => APIKeyController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/keys/create',
        'controller' => APIKeyController::class,
        'action' => 'create',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/keys',
        'controller' => APIKeyController::class,
        'action' => 'store',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/keys/{id}',
        'controller' => APIKeyController::class,
        'action' => 'show',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/keys/{id}/rotate',
        'controller' => APIKeyController::class,
        'action' => 'rotate',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/keys/{id}/revoke',
        'controller' => APIKeyController::class,
        'action' => 'revoke',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Billing Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/billing',
        'controller' => BillingController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/billing/plans',
        'controller' => BillingController::class,
        'action' => 'plans',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/billing/switch-type',
        'controller' => BillingController::class,
        'action' => 'switchBillingType',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/billing/subscribe/{id}',
        'controller' => BillingController::class,
        'action' => 'subscribePlan',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/billing/cancel-plan',
        'controller' => BillingController::class,
        'action' => 'cancelPlan',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/billing/history',
        'controller' => BillingController::class,
        'action' => 'history',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/billing/add-credits',
        'controller' => BillingController::class,
        'action' => 'addCredits',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/billing/process-payment',
        'controller' => BillingController::class,
        'action' => 'processPayment',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Deposit Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/billing/deposit',
        'controller' => DepositController::class,
        'action' => 'showDeposit',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/billing/deposit',
        'controller' => DepositController::class,
        'action' => 'createDeposit',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/billing/deposit/{id}',
        'controller' => DepositController::class,
        'action' => 'showDepositDetail',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/billing/pending',
        'controller' => DepositController::class,
        'action' => 'pendingDeposits',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/billing/deposit/{id}/cancel',
        'controller' => DepositController::class,
        'action' => 'cancelDeposit',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Analytics Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/analytics',
        'controller' => AnalyticsController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/analytics/export',
        'controller' => AnalyticsController::class,
        'action' => 'export',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Support Tickets Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/tickets',
        'controller' => TicketController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/tickets/create',
        'controller' => TicketController::class,
        'action' => 'create',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/tickets',
        'controller' => TicketController::class,
        'action' => 'store',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/tickets/{id}',
        'controller' => TicketController::class,
        'action' => 'show',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/tickets/{id}/reply',
        'controller' => TicketController::class,
        'action' => 'reply',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/tickets/{id}/close',
        'controller' => TicketController::class,
        'action' => 'close',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Referral Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/referral',
        'controller' => ReferralController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/referral/generate',
        'controller' => ReferralController::class,
        'action' => 'generateCode',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/referral/withdraw',
        'controller' => ReferralController::class,
        'action' => 'withdraw',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Admin Routes (Admin Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin',
        'controller' => AdminController::class,
        'action' => 'dashboard',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/dashboard',
        'controller' => AdminController::class,
        'action' => 'dashboard',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/users',
        'controller' => AdminController::class,
        'action' => 'users',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/users/{id}',
        'controller' => AdminController::class,
        'action' => 'userDetail',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/users/{id}/ban',
        'controller' => AdminController::class,
        'action' => 'banUser',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/users/{id}/unban',
        'controller' => AdminController::class,
        'action' => 'unbanUser',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/users/{id}/balance',
        'controller' => AdminController::class,
        'action' => 'updateUserBalance',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/users/{id}/plan',
        'controller' => AdminController::class,
        'action' => 'updateUserPlan',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/deposits',
        'controller' => AdminController::class,
        'action' => 'deposits',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/deposits/{id}',
        'controller' => AdminController::class,
        'action' => 'depositDetail',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/deposits/{id}/approve',
        'controller' => AdminController::class,
        'action' => 'approveDeposit',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/deposits/{id}/reject',
        'controller' => AdminController::class,
        'action' => 'rejectDeposit',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/tickets',
        'controller' => AdminTicketController::class,
        'action' => 'index',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/tickets/{id}',
        'controller' => AdminTicketController::class,
        'action' => 'show',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/tickets/{id}/reply',
        'controller' => AdminTicketController::class,
        'action' => 'reply',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/tickets/{id}/status',
        'controller' => AdminTicketController::class,
        'action' => 'updateStatus',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/tickets/{id}/assign',
        'controller' => AdminTicketController::class,
        'action' => 'assign',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/tickets/{id}/close',
        'controller' => AdminTicketController::class,
        'action' => 'close',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/tickets/{id}/reopen',
        'controller' => AdminTicketController::class,
        'action' => 'reopen',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/tickets/bulk',
        'controller' => AdminTicketController::class,
        'action' => 'bulkAction',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/logs',
        'controller' => AdminController::class,
        'action' => 'auditLogs',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/transactions',
        'controller' => AdminController::class,
        'action' => 'transactions',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/settings',
        'controller' => AdminController::class,
        'action' => 'settings',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/settings',
        'controller' => AdminController::class,
        'action' => 'updateSettings',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Plans Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/plans',
        'controller' => AdminController::class,
        'action' => 'plans',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/plans/create',
        'controller' => AdminController::class,
        'action' => 'createPlan',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/plans',
        'controller' => AdminController::class,
        'action' => 'storePlan',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/plans/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editPlan',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/plans/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updatePlan',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/plans/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deletePlan',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Providers Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/providers',
        'controller' => AdminController::class,
        'action' => 'providers',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/providers/create',
        'controller' => AdminController::class,
        'action' => 'createProvider',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/providers',
        'controller' => AdminController::class,
        'action' => 'storeProvider',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/providers/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editProvider',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/providers/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updateProvider',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/providers/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deleteProvider',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Model Pricing Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/model-pricing',
        'controller' => AdminController::class,
        'action' => 'modelPricing',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/model-pricing/create',
        'controller' => AdminController::class,
        'action' => 'createModelPricing',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/model-pricing',
        'controller' => AdminController::class,
        'action' => 'storeModelPricing',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/model-pricing/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editModelPricing',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/model-pricing/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updateModelPricing',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/model-pricing/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deleteModelPricing',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Themes Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/themes',
        'controller' => AdminController::class,
        'action' => 'themes',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/themes/create',
        'controller' => AdminController::class,
        'action' => 'createTheme',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/themes',
        'controller' => AdminController::class,
        'action' => 'storeTheme',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/themes/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editTheme',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/themes/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updateTheme',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/themes/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deleteTheme',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/themes/{id}/default',
        'controller' => AdminController::class,
        'action' => 'setDefaultTheme',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Custom Pages Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/pages',
        'controller' => AdminController::class,
        'action' => 'pages',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/pages/create',
        'controller' => AdminController::class,
        'action' => 'createPage',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/pages',
        'controller' => AdminController::class,
        'action' => 'storePage',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/pages/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editPage',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/pages/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updatePage',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/pages/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deletePage',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/pages/{id}/toggle-publish',
        'controller' => AdminController::class,
        'action' => 'togglePublishPage',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Menu Items Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/menu',
        'controller' => AdminController::class,
        'action' => 'menuItems',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/menu/create',
        'controller' => AdminController::class,
        'action' => 'createMenuItem',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/menu',
        'controller' => AdminController::class,
        'action' => 'storeMenuItem',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/menu/reorder',
        'controller' => AdminController::class,
        'action' => 'reorderMenuItems',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/menu/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editMenuItem',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/menu/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updateMenuItem',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/menu/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deleteMenuItem',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Notifications Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/notifications',
        'controller' => AdminController::class,
        'action' => 'adminNotifications',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/notifications/send',
        'controller' => AdminController::class,
        'action' => 'sendNotification',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/notifications',
        'controller' => AdminController::class,
        'action' => 'storeNotification',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/notifications/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deleteAdminNotification',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Coupons Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/coupons',
        'controller' => AdminController::class,
        'action' => 'coupons',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/coupons/create',
        'controller' => AdminController::class,
        'action' => 'createCoupon',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/coupons',
        'controller' => AdminController::class,
        'action' => 'storeCoupon',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/coupons/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editCoupon',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/coupons/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updateCoupon',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/coupons/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deleteCoupon',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/coupons/{id}/toggle',
        'controller' => AdminController::class,
        'action' => 'toggleCoupon',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/coupons/{id}/stats',
        'controller' => AdminController::class,
        'action' => 'couponStats',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Changelog Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/changelogs',
        'controller' => AdminController::class,
        'action' => 'changelogs',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/changelogs/create',
        'controller' => AdminController::class,
        'action' => 'createChangelog',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/changelogs',
        'controller' => AdminController::class,
        'action' => 'storeChangelog',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/changelogs/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editChangelog',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/changelogs/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updateChangelog',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/changelogs/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deleteChangelog',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // User Coupon Routes
    // =====================
    [
        'method' => 'POST',
        'path' => '/coupon/validate',
        'controller' => CouponController::class,
        'action' => 'validateCode',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Theme Routes (Public - saves to session/user)
    // =====================
    [
        'method' => 'POST',
        'path' => '/theme/set',
        'controller' => ThemeController::class,
        'action' => 'set',
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/theme/variables',
        'controller' => ThemeController::class,
        'action' => 'getVariables',
        'middleware' => []
    ],

    // =====================
    // Custom Pages Routes (Public)
    // =====================
    [
        'method' => 'GET',
        'path' => '/pages',
        'controller' => PageController::class,
        'action' => 'index',
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/page/{slug}',
        'controller' => PageController::class,
        'action' => 'show',
        'middleware' => []
    ],

    // =====================
    // Notifications Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/notifications',
        'controller' => NotificationController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/notifications/{id}/read',
        'controller' => NotificationController::class,
        'action' => 'markRead',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/notifications/read-all',
        'controller' => NotificationController::class,
        'action' => 'markAllRead',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/notifications/{id}/delete',
        'controller' => NotificationController::class,
        'action' => 'delete',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Webhooks Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/webhooks',
        'controller' => WebhookController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/webhooks/create',
        'controller' => WebhookController::class,
        'action' => 'create',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/webhooks',
        'controller' => WebhookController::class,
        'action' => 'store',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/webhooks/{id}/edit',
        'controller' => WebhookController::class,
        'action' => 'edit',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/webhooks/{id}',
        'controller' => WebhookController::class,
        'action' => 'update',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/webhooks/{id}/delete',
        'controller' => WebhookController::class,
        'action' => 'delete',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/webhooks/{id}/logs',
        'controller' => WebhookController::class,
        'action' => 'logs',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/webhooks/{id}/test',
        'controller' => WebhookController::class,
        'action' => 'test',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/webhooks/{id}/toggle',
        'controller' => WebhookController::class,
        'action' => 'toggle',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Public Changelog Route
    // =====================
    [
        'method' => 'GET',
        'path' => '/changelog',
        'controller' => ChangelogController::class,
        'action' => 'index',
        'middleware' => []
    ],

    // =====================
    // API Playground Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/playground',
        'controller' => PlaygroundController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/playground/execute',
        'controller' => PlaygroundController::class,
        'action' => 'execute',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Security Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/security/login-history',
        'controller' => SecurityController::class,
        'action' => 'loginHistory',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/security/sessions',
        'controller' => SecurityController::class,
        'action' => 'sessions',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/security/sessions/{id}/terminate',
        'controller' => SecurityController::class,
        'action' => 'terminateSession',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/security/sessions/terminate-all',
        'controller' => SecurityController::class,
        'action' => 'terminateAllSessions',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Export Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/export/usage',
        'controller' => ExportController::class,
        'action' => 'usageLogs',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/export/transactions',
        'controller' => ExportController::class,
        'action' => 'transactions',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Status Page (Public)
    // =====================
    [
        'method' => 'GET',
        'path' => '/status',
        'controller' => StatusController::class,
        'action' => 'index',
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/api/health',
        'controller' => StatusController::class,
        'action' => 'checkHealth',
        'middleware' => []
    ],

    // =====================
    // Admin Email Templates Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/email-templates',
        'controller' => AdminController::class,
        'action' => 'emailTemplates',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/email-templates/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editEmailTemplate',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/email-templates/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updateEmailTemplate',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/email-templates/{id}/reset',
        'controller' => AdminController::class,
        'action' => 'resetEmailTemplate',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Incidents Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/incidents',
        'controller' => AdminController::class,
        'action' => 'incidents',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/incidents/create',
        'controller' => AdminController::class,
        'action' => 'createIncident',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/incidents',
        'controller' => AdminController::class,
        'action' => 'storeIncident',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/incidents/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editIncident',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/incidents/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updateIncident',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/incidents/{id}/resolve',
        'controller' => AdminController::class,
        'action' => 'resolveIncident',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/incidents/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deleteIncident',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Role Management Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/roles',
        'controller' => AdminController::class,
        'action' => 'roles',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/roles/create',
        'controller' => AdminController::class,
        'action' => 'createRole',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/roles',
        'controller' => AdminController::class,
        'action' => 'storeRole',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/roles/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editRole',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/roles/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updateRole',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/roles/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deleteRole',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Impersonation Routes
    // =====================
    [
        'method' => 'POST',
        'path' => '/admin/impersonate/{id}',
        'controller' => ImpersonateController::class,
        'action' => 'impersonate',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/exit-impersonation',
        'controller' => ImpersonateController::class,
        'action' => 'exitImpersonation',
        'middleware' => []
    ],

    // =====================
    // Admin System Health Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/health',
        'controller' => HealthController::class,
        'action' => 'index',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/health/refresh',
        'controller' => HealthController::class,
        'action' => 'refresh',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Admin Scheduled Maintenance Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/maintenance',
        'controller' => AdminController::class,
        'action' => 'scheduledMaintenance',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/maintenance/create',
        'controller' => AdminController::class,
        'action' => 'createMaintenance',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/maintenance',
        'controller' => AdminController::class,
        'action' => 'storeMaintenance',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/maintenance/{id}/edit',
        'controller' => AdminController::class,
        'action' => 'editMaintenance',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/maintenance/{id}/update',
        'controller' => AdminController::class,
        'action' => 'updateMaintenance',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/maintenance/{id}/delete',
        'controller' => AdminController::class,
        'action' => 'deleteMaintenance',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/maintenance/{id}/toggle',
        'controller' => AdminController::class,
        'action' => 'toggleMaintenance',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Telegram Integration Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/profile/telegram',
        'controller' => TelegramController::class,
        'action' => 'showLink',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/telegram/link',
        'controller' => TelegramController::class,
        'action' => 'generateLink',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/telegram/unlink',
        'controller' => TelegramController::class,
        'action' => 'unlink',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/telegram/webhook',
        'controller' => TelegramController::class,
        'action' => 'webhook',
        'middleware' => []
    ],

    // =====================
    // Invoice Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/invoice/deposit/{id}',
        'controller' => InvoiceController::class,
        'action' => 'depositInvoice',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/invoice/purchase/{id}',
        'controller' => InvoiceController::class,
        'action' => 'purchaseReceipt',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Auto Top-Up Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/billing/auto-topup',
        'controller' => AutoTopupController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/billing/auto-topup',
        'controller' => AutoTopupController::class,
        'action' => 'update',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/billing/auto-topup/toggle',
        'controller' => AutoTopupController::class,
        'action' => 'toggle',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Gift Code Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/giftcode',
        'controller' => GiftCodeController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/giftcode/redeem',
        'controller' => GiftCodeController::class,
        'action' => 'redeem',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/giftcode/history',
        'controller' => GiftCodeController::class,
        'action' => 'history',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Admin Gift Code Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/giftcodes',
        'controller' => AdminGiftCodeController::class,
        'action' => 'index',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/giftcodes/create',
        'controller' => AdminGiftCodeController::class,
        'action' => 'create',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/giftcodes/store',
        'controller' => AdminGiftCodeController::class,
        'action' => 'store',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/giftcodes/generate-bulk',
        'controller' => AdminGiftCodeController::class,
        'action' => 'generateBulk',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/giftcodes/{id}',
        'controller' => AdminGiftCodeController::class,
        'action' => 'show',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/giftcodes/{id}/toggle',
        'controller' => AdminGiftCodeController::class,
        'action' => 'toggle',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Daily Check-in Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/checkin',
        'controller' => CheckinController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/checkin',
        'controller' => CheckinController::class,
        'action' => 'checkin',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Achievement Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/achievements',
        'controller' => AchievementController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Favorites Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/favorites',
        'controller' => FavoriteController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/favorites/toggle',
        'controller' => FavoriteController::class,
        'action' => 'toggle',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Usage Alerts Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/settings/alerts',
        'controller' => AlertController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/settings/alerts',
        'controller' => AlertController::class,
        'action' => 'save',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Leaderboard Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/leaderboard',
        'controller' => LeaderboardController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/api/leaderboard/{type}',
        'controller' => LeaderboardController::class,
        'action' => 'getData',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Live Chat Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/api/chat/messages',
        'controller' => ChatController::class,
        'action' => 'getMessages',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/api/chat/send',
        'controller' => ChatController::class,
        'action' => 'send',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/api/chat/status',
        'controller' => ChatController::class,
        'action' => 'getStatus',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Admin Chat Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/chat',
        'controller' => AdminChatController::class,
        'action' => 'index',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/chat/{userId}',
        'controller' => AdminChatController::class,
        'action' => 'conversation',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/chat/{userId}/send',
        'controller' => AdminChatController::class,
        'action' => 'send',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/api/admin/chat/unread',
        'controller' => AdminChatController::class,
        'action' => 'getUnreadCount',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // Feedback Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/feedback',
        'controller' => FeedbackController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/feedback',
        'controller' => FeedbackController::class,
        'action' => 'store',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Admin Feedback Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/admin/feedback',
        'controller' => AdminFeedbackController::class,
        'action' => 'index',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'DELETE',
        'path' => '/admin/feedback/{id}',
        'controller' => AdminFeedbackController::class,
        'action' => 'delete',
        'middleware' => [AdminMiddleware::class]
    ],

    // =====================
    // API Key Templates Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/api-keys/templates',
        'controller' => KeyTemplateController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/api-keys/templates',
        'controller' => KeyTemplateController::class,
        'action' => 'store',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/api-keys/templates/{id}',
        'controller' => KeyTemplateController::class,
        'action' => 'show',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/api-keys/templates/{id}/apply',
        'controller' => KeyTemplateController::class,
        'action' => 'apply',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/api-keys/templates/{id}/update',
        'controller' => KeyTemplateController::class,
        'action' => 'update',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'DELETE',
        'path' => '/api-keys/templates/{id}',
        'controller' => KeyTemplateController::class,
        'action' => 'delete',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/api-keys/templates/{id}/default',
        'controller' => KeyTemplateController::class,
        'action' => 'setDefault',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Quick Search Routes (Auth Required)
    // =====================
    [
        'method' => 'GET',
        'path' => '/api/search',
        'controller' => SearchController::class,
        'action' => 'search',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/api/search/recent',
        'controller' => SearchController::class,
        'action' => 'recent',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'DELETE',
        'path' => '/api/search/history',
        'controller' => SearchController::class,
        'action' => 'clearHistory',
        'middleware' => [AuthMiddleware::class]
    ],

    // =====================
    // Notification API Routes
    // =====================
    [
        'method' => 'GET',
        'path' => '/api/notifications/unread-count',
        'controller' => NotificationController::class,
        'action' => 'getUnreadCount',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/api/notifications/recent',
        'controller' => NotificationController::class,
        'action' => 'getRecent',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/api/notifications',
        'controller' => NotificationController::class,
        'action' => 'list',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/api/notifications/counts',
        'controller' => NotificationController::class,
        'action' => 'getCounts',
        'middleware' => [AuthMiddleware::class]
    ],
    [
        'method' => 'DELETE',
        'path' => '/api/notifications/delete-read',
        'controller' => NotificationController::class,
        'action' => 'deleteRead',
        'middleware' => [AuthMiddleware::class]
    ],
];

return $routes;
