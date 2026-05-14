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
];

return $routes;
