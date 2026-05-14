<?php
/**
 * Check-in Controller
 * Handles daily check-in functionality
 */
class CheckinController extends BaseController
{
    private CheckinService $checkinService;

    public function __construct()
    {
        $this->currentPage = 'checkin';
        $this->checkinService = new CheckinService();
    }

    /**
     * Show check-in page with calendar
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Get year and month from query params
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('m');

        // Validate month/year
        if ($month < 1 || $month > 12) $month = (int) date('m');
        if ($year < 2020 || $year > 2100) $year = (int) date('Y');

        $checkinData = $this->checkinService->getCheckinData($userId);
        $calendarData = $this->checkinService->getCalendarData($userId, $year, $month);

        $this->render('checkin/index', [
            'pageTitle' => __('checkin.title', 'Diem danh hang ngay'),
            'checkinData' => $checkinData,
            'calendarData' => $calendarData
        ], ['pages/checkin'], ['pages/checkin']);
    }

    /**
     * Perform check-in (AJAX)
     */
    public function checkin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Vui long dang nhap'], 401);
            return;
        }

        $result = $this->checkinService->checkin($userId);
        $this->json($result);
    }
}
