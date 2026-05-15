<?php
/**
 * Analytics Controller
 * Handles usage statistics, analytics views, and chart data APIs
 */

class AnalyticsController extends BaseController
{
    private AuthService $authService;
    private AnalyticsService $analyticsService;
    private UsageLog $usageLogModel;
    private ApiKey $apiKeyModel;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        
        $this->analyticsService = new AnalyticsService();
        $this->usageLogModel = new UsageLog();
        $this->apiKeyModel = new ApiKey();
    }

    /**
     * Show analytics dashboard
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = $user['id'];

        // Get date range from query params
        $days = (int) ($_GET['days'] ?? 30);
        if (!in_array($days, [7, 30, 90])) {
            $days = 30;
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime("-{$days} days"));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Get summary statistics
        $summaryStats = $this->analyticsService->getSummaryStats($userId);

        // Get daily statistics for initial chart render
        $dailyStats = $this->analyticsService->getDailyUsage($userId, $days);

        // Get model breakdown
        $modelStats = $this->analyticsService->getModelBreakdown($userId, $days);

        // Get hourly distribution
        $hourlyStats = $this->analyticsService->getHourlyDistribution($userId, $days);

        // Get endpoint breakdown
        $endpointStats = $this->analyticsService->getEndpointBreakdown($userId, $days);

        // Get API keys for filtering
        $apiKeys = $this->apiKeyModel->findByUser($userId);

        // Get per-key statistics
        $keyStats = [];
        foreach ($apiKeys as $key) {
            $keyStats[$key['id']] = $this->usageLogModel->getStatsByKey($key['id'], $startDate, $endDate);
            $keyStats[$key['id']]['name'] = $key['name'];
        }

        $this->currentPage = 'analytics';
        $this->render('analytics/index', [
            'pageTitle' => 'Analytics Dashboard',
            'currentPage' => $this->currentPage,
            'summaryStats' => $summaryStats,
            'dailyStats' => $dailyStats,
            'modelStats' => $modelStats,
            'hourlyStats' => $hourlyStats,
            'endpointStats' => $endpointStats,
            'apiKeys' => $apiKeys,
            'keyStats' => $keyStats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'days' => $days
        ], ['analytics'], ['analytics']);
    }

    /**
     * API endpoint: GET /api/analytics/usage
     * Returns daily usage data for line charts
     */
    public function getUsageData(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $days = (int) ($_GET['days'] ?? 7);
        if (!in_array($days, [7, 30, 90])) {
            $days = 7;
        }

        $data = $this->analyticsService->getDailyUsage($user['id'], $days);

        $this->json([
            'labels' => array_column($data, 'date'),
            'datasets' => [
                [
                    'label' => 'API Calls',
                    'data' => array_map('intval', array_column($data, 'api_calls')),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'yAxisID' => 'y'
                ],
                [
                    'label' => 'Tokens Used',
                    'data' => array_map('intval', array_column($data, 'tokens_used')),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'yAxisID' => 'y1'
                ]
            ]
        ]);
    }

    /**
     * API endpoint: GET /api/analytics/models
     * Returns model usage breakdown for pie chart
     */
    public function getModelBreakdown(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $days = (int) ($_GET['days'] ?? 30);

        $data = $this->analyticsService->getModelBreakdown($user['id'], $days);

        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

        $this->json([
            'labels' => array_column($data, 'model'),
            'data' => array_map('intval', array_column($data, 'count')),
            'colors' => array_slice($colors, 0, count($data))
        ]);
    }

    /**
     * API endpoint: GET /api/analytics/costs
     * Returns daily cost data for bar chart
     */
    public function getCostData(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $days = (int) ($_GET['days'] ?? 30);

        $data = $this->analyticsService->getDailyCosts($user['id'], $days);

        $this->json([
            'labels' => array_column($data, 'date'),
            'data' => array_map('floatval', array_column($data, 'cost'))
        ]);
    }

    /**
     * API endpoint: GET /api/analytics/stats
     * Returns summary statistics
     */
    public function getStats(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $stats = $this->analyticsService->getSummaryStats($user['id']);

        $this->json($stats);
    }

    /**
     * API endpoint: GET /api/analytics/hourly
     * Returns hourly distribution data
     */
    public function getHourlyDistribution(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $days = (int) ($_GET['days'] ?? 30);

        $data = $this->analyticsService->getHourlyDistribution($user['id'], $days);

        // Format hours as labels
        $labels = [];
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
        }

        $this->json([
            'labels' => $labels,
            'data' => array_map('intval', array_column($data, 'count'))
        ]);
    }

    /**
     * Export analytics data as CSV
     */
    public function export(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = $user['id'];
        $type = $_GET['type'] ?? 'daily';
        $days = (int) ($_GET['days'] ?? 30);

        // Get date range
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime("-{$days} days"));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Get data based on type
        switch ($type) {
            case 'daily':
                $data = $this->analyticsService->getDailyUsage($userId, $days);
                $filename = 'daily_usage_' . date('Y-m-d') . '.csv';
                $headers = ['Date', 'API Calls', 'Tokens Used', 'Input Tokens', 'Output Tokens', 'Cost', 'Avg Response Time (ms)'];
                break;
            case 'models':
                $data = $this->analyticsService->getModelBreakdown($userId, $days);
                $filename = 'model_usage_' . date('Y-m-d') . '.csv';
                $headers = ['Model', 'Requests', 'Tokens', 'Cost'];
                break;
            case 'hourly':
                $data = $this->analyticsService->getHourlyDistribution($userId, $days);
                $filename = 'hourly_usage_' . date('Y-m-d') . '.csv';
                $headers = ['Hour', 'Requests', 'Tokens', 'Cost'];
                break;
            case 'logs':
                $data = $this->usageLogModel->getRecentByUser($userId, 1000);
                $filename = 'usage_logs_' . date('Y-m-d') . '.csv';
                $headers = ['Date', 'API Key ID', 'Endpoint', 'Model', 'Tokens', 'Cost', 'Response Code', 'Response Time (ms)'];
                break;
            default:
                $this->setFlash('error', 'Invalid export type');
                $this->redirect('/analytics');
                return;
        }

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);

        foreach ($data as $row) {
            switch ($type) {
                case 'daily':
                    fputcsv($output, [
                        $row['date'] ?? '',
                        $row['api_calls'] ?? 0,
                        $row['tokens_used'] ?? 0,
                        $row['input_tokens'] ?? 0,
                        $row['output_tokens'] ?? 0,
                        number_format($row['cost'] ?? 0, 6),
                        number_format($row['avg_response_time'] ?? 0, 2)
                    ]);
                    break;
                case 'models':
                    fputcsv($output, [
                        $row['model'] ?? '',
                        $row['count'] ?? 0,
                        $row['tokens'] ?? 0,
                        number_format($row['cost'] ?? 0, 6)
                    ]);
                    break;
                case 'hourly':
                    fputcsv($output, [
                        sprintf('%02d:00', $row['hour'] ?? 0),
                        $row['count'] ?? 0,
                        $row['tokens'] ?? 0,
                        number_format($row['cost'] ?? 0, 6)
                    ]);
                    break;
                case 'logs':
                    fputcsv($output, [
                        $row['created_at'] ?? '',
                        $row['api_key_id'] ?? '',
                        $row['endpoint'] ?? '',
                        $row['model'] ?? '',
                        $row['tokens_used'] ?? 0,
                        number_format($row['cost'] ?? 0, 6),
                        $row['response_code'] ?? '',
                        $row['response_time_ms'] ?? ''
                    ]);
                    break;
            }
        }

        fclose($output);
        exit;
    }
}
