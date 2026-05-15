<?php
/**
 * Admin Campaign Controller
 * Handles campaign management for admins
 */
class AdminCampaignController extends BaseController
{
    private CampaignService $campaignService;
    private AuthService $authService;

    public function __construct()
    {
        $this->campaignService = new CampaignService();
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
    }

    /**
     * List all campaigns
     * GET /admin/campaigns
     */
    public function index(): void
    {
        $campaigns = $this->campaignService->getAll();
        $stats = $this->campaignService->getStats();

        $this->currentPage = 'admin-campaigns';
        $this->render('admin/campaigns/index', [
            'pageTitle' => 'Quan ly Chien dich',
            'currentPage' => $this->currentPage,
            'campaigns' => $campaigns,
            'stats' => $stats
        ]);
    }

    /**
     * Show create form
     * GET /admin/campaigns/create
     */
    public function create(): void
    {
        $this->currentPage = 'admin-campaigns';
        $this->render('admin/campaigns/create', [
            'pageTitle' => 'Tao Chien dich moi',
            'currentPage' => $this->currentPage
        ]);
    }

    /**
     * Store new campaign
     * POST /admin/campaigns
     */
    public function store(): void
    {
        $user = $this->authService->user();

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'bonus_tokens' => floatval($_POST['bonus_tokens'] ?? 0),
            'bonus_credits' => floatval($_POST['bonus_credits'] ?? 0),
            'max_registrations' => intval($_POST['max_registrations'] ?? 0),
            'starts_at' => !empty($_POST['starts_at']) ? $_POST['starts_at'] : null,
            'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
            'created_by' => $user['id'] ?? null
        ];

        // Validation
        if (empty($data['name'])) {
            $this->setFlash('error', 'Ten chien dich khong duoc de trong');
            $this->redirect('/admin/campaigns/create');
            return;
        }

        $result = $this->campaignService->create($data);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
            $this->redirect('/admin/campaigns');
        } else {
            $this->setFlash('error', $result['message']);
            $this->redirect('/admin/campaigns/create');
        }
    }

    /**
     * Show edit form
     * GET /admin/campaigns/{id}/edit
     */
    public function edit(int $id): void
    {
        $campaign = $this->campaignService->find($id);

        if (!$campaign) {
            $this->setFlash('error', 'Chien dich khong ton tai');
            $this->redirect('/admin/campaigns');
            return;
        }

        $this->currentPage = 'admin-campaigns';
        $this->render('admin/campaigns/edit', [
            'pageTitle' => 'Chinh sua Chien dich',
            'currentPage' => $this->currentPage,
            'campaign' => $campaign
        ]);
    }

    /**
     * Update campaign
     * POST /admin/campaigns/{id}
     */
    public function update(int $id): void
    {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'bonus_tokens' => floatval($_POST['bonus_tokens'] ?? 0),
            'bonus_credits' => floatval($_POST['bonus_credits'] ?? 0),
            'max_registrations' => intval($_POST['max_registrations'] ?? 0),
            'starts_at' => !empty($_POST['starts_at']) ? $_POST['starts_at'] : null,
            'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null
        ];

        // Validation
        if (empty($data['name'])) {
            $this->setFlash('error', 'Ten chien dich khong duoc de trong');
            $this->redirect('/admin/campaigns/' . $id . '/edit');
            return;
        }

        $result = $this->campaignService->update($id, $data);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
            $this->redirect('/admin/campaigns');
        } else {
            $this->setFlash('error', $result['message']);
            $this->redirect('/admin/campaigns/' . $id . '/edit');
        }
    }

    /**
     * Toggle campaign status
     * POST /admin/campaigns/{id}/toggle
     */
    public function toggle(int $id): void
    {
        $result = $this->campaignService->toggle($id);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect('/admin/campaigns');
    }

    /**
     * View campaign registrations
     * GET /admin/campaigns/{id}/registrations
     */
    public function registrations(int $id): void
    {
        $campaign = $this->campaignService->find($id);

        if (!$campaign) {
            $this->setFlash('error', 'Chien dich khong ton tai');
            $this->redirect('/admin/campaigns');
            return;
        }

        $registrations = $this->campaignService->getRegistrations($id);

        $this->currentPage = 'admin-campaigns';
        $this->render('admin/campaigns/registrations', [
            'pageTitle' => 'Danh sach dang ky - ' . $campaign['name'],
            'currentPage' => $this->currentPage,
            'campaign' => $campaign,
            'registrations' => $registrations
        ]);
    }

    /**
     * Delete campaign
     * DELETE /admin/campaigns/{id}
     */
    public function delete(int $id): void
    {
        $result = $this->campaignService->delete($id);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect('/admin/campaigns');
    }
}
