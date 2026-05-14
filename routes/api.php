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
];

return $routes;
