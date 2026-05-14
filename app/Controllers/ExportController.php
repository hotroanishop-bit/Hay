<?php
/**
 * Export Controller
 * Handles data export to CSV format
 */

class ExportController extends BaseController
{
    private ExportService $exportService;
    private AuthService $authService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->exportService = new ExportService();
    }

    /**
     * Export usage logs to CSV
     * GET /export/usage?start=YYYY-MM-DD&end=YYYY-MM-DD
     */
    public function usageLogs(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $startDate = $_GET['start'] ?? $_GET['start_date'] ?? null;
        $endDate = $_GET['end'] ?? $_GET['end_date'] ?? null;

        // Validate dates
        if ($startDate && !$this->isValidDate($startDate)) {
            $this->setFlash('error', 'Invalid start date format');
            $this->redirect('/analytics');
            return;
        }

        if ($endDate && !$this->isValidDate($endDate)) {
            $this->setFlash('error', 'Invalid end date format');
            $this->redirect('/analytics');
            return;
        }

        // Generate CSV content
        $csv = $this->exportService->exportUsageLogs($user['id'], $startDate, $endDate);
        $filename = $this->exportService->generateFilename('usage_logs', $startDate, $endDate);

        // Stream download
        $this->exportService->streamDownload($filename, $csv);
    }

    /**
     * Export transactions to CSV
     * GET /export/transactions?start=YYYY-MM-DD&end=YYYY-MM-DD
     */
    public function transactions(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $startDate = $_GET['start'] ?? $_GET['start_date'] ?? null;
        $endDate = $_GET['end'] ?? $_GET['end_date'] ?? null;

        // Validate dates
        if ($startDate && !$this->isValidDate($startDate)) {
            $this->setFlash('error', 'Invalid start date format');
            $this->redirect('/billing/history');
            return;
        }

        if ($endDate && !$this->isValidDate($endDate)) {
            $this->setFlash('error', 'Invalid end date format');
            $this->redirect('/billing/history');
            return;
        }

        // Generate CSV content
        $csv = $this->exportService->exportTransactions($user['id'], $startDate, $endDate);
        $filename = $this->exportService->generateFilename('transactions', $startDate, $endDate);

        // Stream download
        $this->exportService->streamDownload($filename, $csv);
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    private function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
