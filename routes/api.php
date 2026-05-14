<?php
/**
 * API Routes
 * Defines all API routes with their controllers and middleware
 * OpenAI-compatible API endpoints for the proxy gateway
 *
 * Route structure:
 * [
 *     'method' => 'GET' or 'POST',
 *     'path' => '/v1/path/{parameter}',
 *     'controller' => ControllerClass::class,
 *     'action' => 'methodName',
 *     'middleware' => ['MiddlewareClass', ...] (optional)
 * ]
 */

$routes = [
    // =====================
    // OpenAI-Compatible API Routes
    // All routes require API authentication and rate limiting
    // =====================
    
    /**
     * Chat Completions
     * POST /v1/chat/completions
     * Creates a chat completion response for the given messages
     */
    [
        'method' => 'POST',
        'path' => '/v1/chat/completions',
        'controller' => ApiProxyController::class,
        'action' => 'chatCompletions',
        'middleware' => [ApiAuthMiddleware::class, ApiRateLimitMiddleware::class]
    ],

    /**
     * Completions (Legacy)
     * POST /v1/completions
     * Creates a completion for the provided prompt
     */
    [
        'method' => 'POST',
        'path' => '/v1/completions',
        'controller' => ApiProxyController::class,
        'action' => 'completions',
        'middleware' => [ApiAuthMiddleware::class, ApiRateLimitMiddleware::class]
    ],

    /**
     * Embeddings
     * POST /v1/embeddings
     * Creates an embedding vector representing the input text
     */
    [
        'method' => 'POST',
        'path' => '/v1/embeddings',
        'controller' => ApiProxyController::class,
        'action' => 'embeddings',
        'middleware' => [ApiAuthMiddleware::class, ApiRateLimitMiddleware::class]
    ],

    /**
     * List Models
     * GET /v1/models
     * Lists the currently available models
     */
    [
        'method' => 'GET',
        'path' => '/v1/models',
        'controller' => ApiProxyController::class,
        'action' => 'listModels',
        'middleware' => [ApiAuthMiddleware::class, ApiRateLimitMiddleware::class]
    ],

    // =====================
    // Notification API Routes
    // =====================
    
    /**
     * Get unread notification count
     * GET /api/notifications/unread-count
     */
    [
        'method' => 'GET',
        'path' => '/api/notifications/unread-count',
        'controller' => NotificationController::class,
        'action' => 'getUnreadCount',
        'middleware' => []
    ],

    /**
     * Get recent notifications
     * GET /api/notifications/recent
     */
    [
        'method' => 'GET',
        'path' => '/api/notifications/recent',
        'controller' => NotificationController::class,
        'action' => 'getRecent',
        'middleware' => []
    ],

    /**
     * Get notification counts by type
     * GET /api/notifications/counts
     */
    [
        'method' => 'GET',
        'path' => '/api/notifications/counts',
        'controller' => NotificationController::class,
        'action' => 'getCounts',
        'middleware' => []
    ],

    /**
     * Get paginated notifications list
     * GET /api/notifications
     */
    [
        'method' => 'GET',
        'path' => '/api/notifications',
        'controller' => NotificationController::class,
        'action' => 'list',
        'middleware' => []
    ],

    /**
     * Mark notification as read
     * POST /api/notifications/{id}/read
     */
    [
        'method' => 'POST',
        'path' => '/api/notifications/{id}/read',
        'controller' => NotificationController::class,
        'action' => 'markRead',
        'middleware' => []
    ],

    /**
     * Mark all notifications as read
     * POST /api/notifications/mark-all-read
     */
    [
        'method' => 'POST',
        'path' => '/api/notifications/mark-all-read',
        'controller' => NotificationController::class,
        'action' => 'markAllRead',
        'middleware' => []
    ],

    /**
     * Delete a notification
     * DELETE /api/notifications/{id}
     */
    [
        'method' => 'DELETE',
        'path' => '/api/notifications/{id}',
        'controller' => NotificationController::class,
        'action' => 'delete',
        'middleware' => []
    ],

    /**
     * Delete all read notifications
     * DELETE /api/notifications/delete-read
     */
    [
        'method' => 'DELETE',
        'path' => '/api/notifications/delete-read',
        'controller' => NotificationController::class,
        'action' => 'deleteRead',
        'middleware' => []
    ],
];

return $routes;
