<?php
/**
 * Admin Gift Code Controller
 * Handles admin gift code management
 */
class AdminGiftCodeController extends BaseController
{
    private GiftCodeService $giftCodeService;

    public function __construct()
    {
        $this->currentPage = 'admin-giftcodes';
        $this->giftCodeService = new GiftCodeService();
    }

    /**
     * Show all gift codes
     */
    public function index(): void
    {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $filters = [
            'search' => $_GET['search'] ?? '',
            'type' => $_GET['type'] ?? '',
            'is_active' => $_GET['is_active'] ?? ''
        ];

        $giftcodes = $this->giftCodeService->getAll($page, 20, $filters);
        $stats = $this->giftCodeService->getStats();

        $this->render('admin/giftcodes/index', [
            'pageTitle' => __('admin.giftcodes.title', 'Quan ly Gift Codes'),
            'giftcodes' => $giftcodes,
            'stats' => $stats
        ], ['pages/admin'], ['pages/admin']);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->render('admin/giftcodes/create', [
            'pageTitle' => __('admin.giftcodes.create_title', 'Tao Gift Code')
        ], ['pages/admin'], ['pages/admin']);
    }

    /**
     * Store single gift code
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/giftcodes');
            return;
        }

        $adminId = $_SESSION['user_id'] ?? 0;
        
        $data = [
            'code' => $_POST['code'] ?? '',
            'type' => $_POST['type'] ?? 'tokens',
            'value' => floatval($_POST['value'] ?? 0),
            'max_uses' => intval($_POST['max_uses'] ?? 1),
            'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null
        ];

        $result = $this->giftCodeService->createCode($data, $adminId);

        if ($result['success']) {
            $this->setFlash('success', 'Tao gift code thanh cong: ' . $result['code']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect('/admin/giftcodes');
    }

    /**
     * Generate bulk gift codes (AJAX)
     */
    public function generateBulk(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $adminId = $_SESSION['user_id'] ?? 0;
        
        $count = intval($_POST['count'] ?? 10);
        $data = [
            'prefix' => $_POST['prefix'] ?? 'GIFT',
            'length' => intval($_POST['length'] ?? 8),
            'type' => $_POST['type'] ?? 'tokens',
            'value' => floatval($_POST['value'] ?? 0),
            'max_uses' => intval($_POST['max_uses'] ?? 1),
            'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null
        ];

        $result = $this->giftCodeService->bulkCreate($count, $data, $adminId);
        $this->json($result);
    }

    /**
     * Toggle gift code status
     */
    public function toggle(int $id): void
    {
        $this->giftCodeService->toggleStatus($id);
        $this->setFlash('success', 'Cap nhat trang thai thanh cong');
        $this->redirect('/admin/giftcodes');
    }

    /**
     * Show gift code details
     */
    public function show(int $id): void
    {
        $code = $this->giftCodeService->getCodeDetails($id);
        
        if (!$code) {
            $this->setFlash('error', 'Gift code khong ton tai');
            $this->redirect('/admin/giftcodes');
            return;
        }

        $this->render('admin/giftcodes/show', [
            'pageTitle' => 'Chi tiet Gift Code: ' . $code['code'],
            'code' => $code
        ], ['pages/admin'], ['pages/admin']);
    }
}
