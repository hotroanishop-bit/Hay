<?php
/**
 * Campaign Controller
 * Handles public campaign landing pages
 */
class CampaignController extends BaseController
{
    private CampaignService $campaignService;

    public function __construct()
    {
        $this->campaignService = new CampaignService();
    }

    /**
     * Show campaign landing page
     * GET /c/{slug} or /campaign/{slug}
     */
    public function show(string $slug): void
    {
        $campaign = $this->campaignService->findBySlug($slug);
        
        if (!$campaign) {
            $this->render('campaign/not_found', [
                'pageTitle' => 'Chien dich khong ton tai',
                'currentPage' => ''
            ]);
            return;
        }

        // Check if campaign is active
        if (!$this->campaignService->isActive($campaign)) {
            $this->render('campaign/expired', [
                'pageTitle' => 'Chien dich da ket thuc',
                'currentPage' => '',
                'campaign' => $campaign
            ]);
            return;
        }

        // Store campaign slug in session for registration
        $_SESSION['campaign_slug'] = $slug;

        $this->currentPage = 'campaign';
        $this->render('campaign/landing', [
            'pageTitle' => $campaign['name'],
            'currentPage' => $this->currentPage,
            'campaign' => $campaign
        ]);
    }
}
