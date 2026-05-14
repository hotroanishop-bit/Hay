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
        'path' => '/',
        'controller' => DashboardController::class,
        'action' => 'index',
        'middleware' => [AuthMiddleware::class]
    ],
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
        'path' => '/profile/update',
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
        'controller' => AdminController::class,
        'action' => 'tickets',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'GET',
        'path' => '/admin/tickets/{id}',
        'controller' => AdminController::class,
        'action' => 'ticketDetail',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/tickets/{id}/reply',
        'controller' => AdminController::class,
        'action' => 'replyTicket',
        'middleware' => [AdminMiddleware::class]
    ],
    [
        'method' => 'POST',
        'path' => '/admin/tickets/{id}/close',
        'controller' => AdminController::class,
        'action' => 'closeTicket',
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
];

return $routes;
