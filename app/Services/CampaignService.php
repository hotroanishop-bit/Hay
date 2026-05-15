<?php
/**
 * Campaign Service
 * Business logic for registration campaigns
 */
class CampaignService
{
    private Campaign $campaignModel;
    private CampaignRegistration $registrationModel;
    private User $userModel;

    public function __construct()
    {
        $this->campaignModel = new Campaign();
        $this->registrationModel = new CampaignRegistration();
        $this->userModel = new User();
    }

    /**
     * Find campaign by slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->campaignModel->findBySlug($slug);
    }

    /**
     * Find campaign by ID
     */
    public function find(int $id): ?array
    {
        return $this->campaignModel->find($id);
    }

    /**
     * Check if campaign is currently active
     */
    public function isActive(?array $campaign): bool
    {
        if (!$campaign) {
            return false;
        }

        // Check is_active flag
        if (!$campaign['is_active']) {
            return false;
        }

        $now = time();

        // Check start date
        if (!empty($campaign['starts_at'])) {
            $startTime = strtotime($campaign['starts_at']);
            if ($now < $startTime) {
                return false;
            }
        }

        // Check expiry date
        if (!empty($campaign['expires_at'])) {
            $expiryTime = strtotime($campaign['expires_at']);
            if ($now > $expiryTime) {
                return false;
            }
        }

        // Check max registrations
        if ($campaign['max_registrations'] > 0 && 
            $campaign['current_registrations'] >= $campaign['max_registrations']) {
            return false;
        }

        return true;
    }

    /**
     * Apply campaign bonus to user after registration
     */
    public function applyBonus(int $campaignId, int $userId): array
    {
        $campaign = $this->campaignModel->find($campaignId);
        
        if (!$campaign) {
            return [
                'success' => false,
                'message' => 'Chien dich khong ton tai'
            ];
        }

        // Check if campaign is still active
        if (!$this->isActive($campaign)) {
            return [
                'success' => false,
                'message' => 'Chien dich da ket thuc hoac khong hoat dong'
            ];
        }

        // Check if user already got bonus from this campaign
        if ($this->registrationModel->hasUserRegistered($campaignId, $userId)) {
            return [
                'success' => false,
                'message' => 'Ban da nhan thuong tu chien dich nay roi'
            ];
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User khong ton tai'
            ];
        }

        try {
            $this->campaignModel->beginTransaction();

            $totalBonus = 0;

            // Add bonus tokens
            if ($campaign['bonus_tokens'] > 0) {
                $sql = "UPDATE users SET balance = balance + :tokens WHERE id = :user_id";
                $stmt = $this->userModel->db()->prepare($sql);
                $stmt->execute([
                    'tokens' => $campaign['bonus_tokens'],
                    'user_id' => $userId
                ]);
                $totalBonus += $campaign['bonus_tokens'];

                // Record transaction
                $this->recordTransaction($userId, $campaign['bonus_tokens'], 'Campaign bonus tokens: ' . $campaign['name']);
            }

            // Add bonus credits
            if ($campaign['bonus_credits'] > 0) {
                $sql = "UPDATE users SET balance = balance + :credits WHERE id = :user_id";
                $stmt = $this->userModel->db()->prepare($sql);
                $stmt->execute([
                    'credits' => $campaign['bonus_credits'],
                    'user_id' => $userId
                ]);
                $totalBonus += $campaign['bonus_credits'];

                // Record transaction
                $this->recordTransaction($userId, $campaign['bonus_credits'], 'Campaign bonus credits: ' . $campaign['name']);
            }

            // Record campaign registration
            $this->registrationModel->recordRegistration(
                $campaignId,
                $userId,
                $totalBonus,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );

            // Increment campaign registration count
            $this->campaignModel->incrementRegistrations($campaignId);

            // Create notification
            $this->createBonusNotification($userId, $campaign, $totalBonus);

            $this->campaignModel->commit();

            return [
                'success' => true,
                'message' => 'Chuc mung! Ban da nhan duoc ' . number_format($totalBonus, 2) . ' tu chien dich ' . $campaign['name'],
                'bonus' => $totalBonus,
                'campaign' => $campaign['name']
            ];

        } catch (\Exception $e) {
            $this->campaignModel->rollback();
            return [
                'success' => false,
                'message' => 'Loi khi ap dung bonus: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Record transaction for bonus
     */
    private function recordTransaction(int $userId, float $amount, string $description): void
    {
        $sql = "INSERT INTO transactions (user_id, type, amount, description, status, created_at)
                VALUES (:user_id, 'credit', :amount, :description, 'completed', NOW())";
        $stmt = $this->userModel->db()->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'amount' => $amount,
            'description' => $description
        ]);
    }

    /**
     * Create notification for bonus
     */
    private function createBonusNotification(int $userId, array $campaign, float $totalBonus): void
    {
        try {
            $sql = "INSERT INTO user_notifications (user_id, type, title, message, created_at)
                    VALUES (:user_id, 'success', :title, :message, NOW())";
            $stmt = $this->userModel->db()->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'title' => 'Chao mung ban!',
                'message' => 'Ban da nhan duoc ' . number_format($totalBonus, 2) . ' tu chien dich "' . $campaign['name'] . '"'
            ]);
        } catch (\Exception $e) {
            // Silent fail for notification
        }
    }

    /**
     * Get all campaigns
     */
    public function getAll(): array
    {
        return $this->campaignModel->getAllWithCreator();
    }

    /**
     * Get campaign statistics
     */
    public function getStats(?int $campaignId = null): array
    {
        if ($campaignId) {
            $campaign = $this->campaignModel->find($campaignId);
            if (!$campaign) {
                return [];
            }
            
            $registrations = $this->registrationModel->getRegistrationsForCampaign($campaignId);
            return [
                'campaign' => $campaign,
                'registrations' => $registrations,
                'total_registrations' => count($registrations),
                'total_bonus_given' => array_sum(array_column($registrations, 'bonus_received'))
            ];
        }

        return $this->campaignModel->getStats();
    }

    /**
     * Create a new campaign
     */
    public function create(array $data): array
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        } else {
            $data['slug'] = $this->sanitizeSlug($data['slug']);
        }

        // Check slug uniqueness
        if ($this->campaignModel->slugExists($data['slug'])) {
            return [
                'success' => false,
                'message' => 'Slug da ton tai, vui long chon slug khac'
            ];
        }

        try {
            $id = $this->campaignModel->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? '',
                'bonus_tokens' => $data['bonus_tokens'] ?? 0,
                'bonus_credits' => $data['bonus_credits'] ?? 0,
                'max_registrations' => $data['max_registrations'] ?? 0,
                'starts_at' => !empty($data['starts_at']) ? $data['starts_at'] : null,
                'expires_at' => !empty($data['expires_at']) ? $data['expires_at'] : null,
                'is_active' => 1,
                'created_by' => $data['created_by'] ?? null
            ]);

            return [
                'success' => true,
                'message' => 'Tao chien dich thanh cong',
                'id' => $id,
                'slug' => $data['slug']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Loi khi tao chien dich: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update a campaign
     */
    public function update(int $id, array $data): array
    {
        $campaign = $this->campaignModel->find($id);
        if (!$campaign) {
            return [
                'success' => false,
                'message' => 'Chien dich khong ton tai'
            ];
        }

        // Handle slug
        if (!empty($data['slug'])) {
            $data['slug'] = $this->sanitizeSlug($data['slug']);
            if ($this->campaignModel->slugExists($data['slug'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Slug da ton tai, vui long chon slug khac'
                ];
            }
        }

        try {
            $updateData = [];
            $allowedFields = ['name', 'slug', 'description', 'bonus_tokens', 'bonus_credits', 
                             'max_registrations', 'starts_at', 'expires_at'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            $this->campaignModel->update($id, $updateData);

            return [
                'success' => true,
                'message' => 'Cap nhat chien dich thanh cong'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Loi khi cap nhat chien dich: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Toggle campaign active status
     */
    public function toggle(int $id): array
    {
        $campaign = $this->campaignModel->find($id);
        if (!$campaign) {
            return [
                'success' => false,
                'message' => 'Chien dich khong ton tai'
            ];
        }

        $this->campaignModel->toggleActive($id);

        return [
            'success' => true,
            'message' => $campaign['is_active'] ? 'Da tat chien dich' : 'Da bat chien dich'
        ];
    }

    /**
     * Delete campaign
     */
    public function delete(int $id): array
    {
        $campaign = $this->campaignModel->find($id);
        if (!$campaign) {
            return [
                'success' => false,
                'message' => 'Chien dich khong ton tai'
            ];
        }

        // Check if has registrations
        if ($campaign['current_registrations'] > 0) {
            return [
                'success' => false,
                'message' => 'Khong the xoa chien dich da co nguoi dang ky'
            ];
        }

        $this->campaignModel->delete($id);

        return [
            'success' => true,
            'message' => 'Da xoa chien dich'
        ];
    }

    /**
     * Get campaign registrations
     */
    public function getRegistrations(int $campaignId, int $limit = 100, int $offset = 0): array
    {
        return $this->registrationModel->getRegistrationsForCampaign($campaignId, $limit, $offset);
    }

    /**
     * Generate URL-friendly slug from name
     */
    private function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Add random suffix for uniqueness
        $slug .= '-' . substr(md5(uniqid()), 0, 6);
        
        return $slug;
    }

    /**
     * Sanitize user-provided slug
     */
    private function sanitizeSlug(string $slug): string
    {
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
