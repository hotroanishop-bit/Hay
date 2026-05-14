<?php
/**
 * Status Controller
 * Handles public status page and health check API
 */

class StatusController extends BaseController
{
    private HealthCheckService $healthCheckService;
    private Incident $incidentModel;

    public function __construct()
    {
        $this->healthCheckService = new HealthCheckService();
        $this->incidentModel = new Incident();
    }

    /**
     * Display public status page
     */
    public function index(): void
    {
        $overallStatus = $this->healthCheckService->getOverallStatus();
        $components = $this->healthCheckService->getComponentStatuses();
        $activeIncidents = $this->incidentModel->getActive();
        $recentIncidents = $this->incidentModel->getResolved(10);
        $uptime = $this->healthCheckService->getUptimePercentage();

        // Get updates for active incidents
        $incidentsWithUpdates = [];
        foreach ($activeIncidents as $incident) {
            $incident['updates'] = $this->incidentModel->getUpdates($incident['id']);
            $incidentsWithUpdates[] = $incident;
        }

        // Use landing layout for public page
        $this->renderLanding('status/index', [
            'pageTitle' => 'System Status - Hay API Gateway',
            'overallStatus' => $overallStatus,
            'components' => $components,
            'activeIncidents' => $incidentsWithUpdates,
            'recentIncidents' => $recentIncidents,
            'uptime' => $uptime,
            'lastUpdated' => date('M d, Y H:i:s T')
        ], ['status'], []);
    }

    /**
     * Health check JSON API endpoint
     */
    public function checkHealth(): void
    {
        $summary = $this->healthCheckService->getHealthSummary();
        
        // Set appropriate HTTP status code
        $httpStatus = 200;
        if ($summary['status'] === 'degraded') {
            $httpStatus = 200; // Still return 200 for degraded
        } elseif ($summary['status'] === 'outage') {
            $httpStatus = 503; // Service Unavailable
        }

        $this->json($summary, $httpStatus);
    }

    /**
     * Render using landing layout (no sidebar/header)
     */
    protected function renderLanding(string $view, array $data = [], array $pageCssFiles = [], array $pageJsFiles = []): void
    {
        extract($data);
        
        $contentView = VIEWS_PATH . '/pages/' . $view . '.php';
        
        require VIEWS_PATH . '/layouts/landing_layout.php';
    }
}
