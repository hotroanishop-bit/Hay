<?php
/**
 * Key Template Controller
 * Manages API key configuration templates
 */

class KeyTemplateController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
    }

    /**
     * GET /api-keys/templates - List templates
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $db = Database::getInstance();
        $sql = "SELECT * FROM api_key_templates WHERE user_id = ? ORDER BY is_default DESC, name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user['id']]);
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode settings JSON
        foreach ($templates as &$template) {
            $template['settings'] = json_decode($template['settings'], true) ?? [];
        }

        $this->currentPage = 'keys';
        $this->render('keys/templates', [
            'pageTitle' => 'API Key Templates',
            'currentPage' => $this->currentPage,
            'templates' => $templates
        ], ['keys'], ['key-templates']);
    }

    /**
     * POST /api-keys/templates - Create template
     */
    public function store(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $settings = $_POST['settings'] ?? [];
        $isDefault = !empty($_POST['is_default']);

        if (empty($name)) {
            $this->json(['error' => 'Template name is required'], 400);
            return;
        }

        if (strlen($name) > 100) {
            $this->json(['error' => 'Template name too long'], 400);
            return;
        }

        $db = Database::getInstance();
        
        // If setting as default, unset other defaults
        if ($isDefault) {
            $updateSql = "UPDATE api_key_templates SET is_default = 0 WHERE user_id = ?";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([$user['id']]);
        }

        $sql = "INSERT INTO api_key_templates (user_id, name, settings, is_default, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $user['id'],
            $name,
            json_encode($settings),
            $isDefault ? 1 : 0
        ]);
        
        $templateId = $db->lastInsertId();

        $this->json([
            'success' => true,
            'template_id' => $templateId,
            'message' => 'Template created successfully'
        ]);
    }

    /**
     * GET /api-keys/templates/{id} - Get template details
     */
    public function show(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::getInstance();
        $sql = "SELECT * FROM api_key_templates WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id, $user['id']]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            $this->json(['error' => 'Template not found'], 404);
            return;
        }
        
        $template['settings'] = json_decode($template['settings'], true) ?? [];

        $this->json([
            'success' => true,
            'template' => $template
        ]);
    }

    /**
     * POST /api-keys/templates/{id}/apply - Apply template to new key
     */
    public function apply(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::getInstance();
        $sql = "SELECT * FROM api_key_templates WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id, $user['id']]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            $this->json(['error' => 'Template not found'], 404);
            return;
        }
        
        $settings = json_decode($template['settings'], true) ?? [];

        $this->json([
            'success' => true,
            'settings' => $settings,
            'template_name' => $template['name']
        ]);
    }

    /**
     * POST /api-keys/templates/{id}/update - Update template
     */
    public function update(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::getInstance();
        
        // Check ownership
        $checkSql = "SELECT id FROM api_key_templates WHERE id = ? AND user_id = ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$id, $user['id']]);
        
        if (!$checkStmt->fetch()) {
            $this->json(['error' => 'Template not found'], 404);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $settings = $_POST['settings'] ?? [];
        $isDefault = !empty($_POST['is_default']);

        if (empty($name)) {
            $this->json(['error' => 'Template name is required'], 400);
            return;
        }

        // If setting as default, unset other defaults
        if ($isDefault) {
            $updateSql = "UPDATE api_key_templates SET is_default = 0 WHERE user_id = ? AND id != ?";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([$user['id'], $id]);
        }

        $sql = "UPDATE api_key_templates SET name = ?, settings = ?, is_default = ?, updated_at = NOW() 
                WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $name,
            json_encode($settings),
            $isDefault ? 1 : 0,
            $id,
            $user['id']
        ]);

        $this->json([
            'success' => true,
            'message' => 'Template updated successfully'
        ]);
    }

    /**
     * DELETE /api-keys/templates/{id} - Delete template
     */
    public function delete(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::getInstance();
        $sql = "DELETE FROM api_key_templates WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id, $user['id']]);
        
        if ($stmt->rowCount() > 0) {
            $this->json(['success' => true, 'message' => 'Template deleted']);
        } else {
            $this->json(['error' => 'Template not found'], 404);
        }
    }

    /**
     * POST /api-keys/templates/{id}/default - Set as default
     */
    public function setDefault(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::getInstance();
        
        // Unset all defaults
        $updateSql = "UPDATE api_key_templates SET is_default = 0 WHERE user_id = ?";
        $updateStmt = $db->prepare($updateSql);
        $updateStmt->execute([$user['id']]);
        
        // Set this as default
        $sql = "UPDATE api_key_templates SET is_default = 1 WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id, $user['id']]);
        
        $this->json(['success' => true]);
    }
}
