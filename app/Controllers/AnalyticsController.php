<?php
/**
 * Analytics Controller
 * Handles usage statistics and analytics views
 */

class AnalyticsController extends BaseController
{
    private AuthService $authService;
    private UsageLog $usageLogModel;
    private ApiKey $apiKeyModel;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        
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
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Get overall statistics
        $overallStats = $this->usageLogModel->getStatsByUser($userId, $startDate, $endDate);

        // Get daily statistics
        $dailyStats = $this->usageLogModel->getDailyStats($userId, 30);

        // Get usage by endpoint
        $endpointStats = $this->usageLogModel->getUsageByEndpoint($userId);

        // Get hourly distribution
        $hourlyStats = $this->usageLogModel->getHourlyDistribution($userId, 7);

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
            'pageTitle' => 'Analytics',
            'currentPage' => $this->currentPage,
            'overallStats' => $overallStats,
            'dailyStats' => $dailyStats,
            'endpointStats' => $endpointStats,
            'hourlyStats' => $hourlyStats,
            'apiKeys' => $apiKeys,
            'keyStats' => $keyStats,
            'startDate' => $startDate,
            'endDate' => $endDate
        ], ['analytics'], ['analytics']);
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
        $format = $_GET['format'] ?? 'csv';
        $type = $_GET['type'] ?? 'daily';

        // Get date range
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Get data based on type
        switch ($type) {
            case 'daily':
                $data = $this->usageLogModel->getDailyStats($userId, 30);
                $filename = 'daily_usage_' . date('Y-m-d') . '.csv';
                $headers = ['Date', 'Requests', 'Tokens', 'Cost'];
                break;
            case 'endpoint':
                $data = $this->usageLogModel->getUsageByEndpoint($userId);
                $filename = 'endpoint_usage_' . date('Y-m-d') . '.csv';
                $headers = ['Endpoint', 'Requests', 'Total Tokens', 'Total Cost'];
                break;
            case 'logs':
                $data = $this->usageLogModel->getRecentByUser($userId, 1000);
                $filename = 'usage_logs_' . date('Y-m-d') . '.csv';
                $headers = ['Date', 'API Key ID', 'Endpoint', 'Tokens', 'Cost', 'Response Code'];
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
                        $row['requests'] ?? 0,
                        $row['tokens'] ?? 0,
                        number_format($row['cost'] ?? 0, 4)
                    ]);
                    break;
                case 'endpoint':
                    fputcsv($output, [
                        $row['endpoint'] ?? '',
                        $row['requests'] ?? 0,
                        $row['total_tokens'] ?? 0,
                        number_format($row['total_cost'] ?? 0, 4)
                    ]);
                    break;
                case 'logs':
                    fputcsv($output, [
                        $row['created_at'] ?? '',
                        $row['api_key_id'] ?? '',
                        $row['endpoint'] ?? '',
                        $row['tokens_used'] ?? 0,
                        number_format($row['cost'] ?? 0, 4),
                        $row['response_code'] ?? ''
                    ]);
                    break;
            }
        }

        fclose($output);
        exit;
    }
}
