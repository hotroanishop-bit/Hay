<?php
/**
 * Permission Service
 * Handles role-based permission checking for admin users
 */

class PermissionService
{
    private AdminRole $roleModel;
    private static ?array $cachedPermissions = null;
    private static ?int $cachedUserId = null;

    public function __construct()
    {
        $this->roleModel = new AdminRole();
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(?array $user, string $permission): bool
    {
        if (!$user || empty($user['is_admin'])) {
            return false;
        }

        // Super admin users without role_id have all permissions
        if (empty($user['role_id'])) {
            return $user['is_admin'] == 1;
        }

        $permissions = $this->getUserPermissions($user);
        return in_array($permission, $permissions);
    }

    /**
     * Get all permissions for a user
     */
    public function getUserPermissions(array $user): array
    {
        if (empty($user['role_id'])) {
            // Admin without role gets all permissions
            if (!empty($user['is_admin'])) {
                return AdminRole::getAllPermissionKeys();
            }
            return [];
        }

        // Use cache if available
        if (self::$cachedUserId === $user['id'] && self::$cachedPermissions !== null) {
            return self::$cachedPermissions;
        }

        $permissions = $this->roleModel->getPermissions($user['role_id']);
        
        // Cache the result
        self::$cachedUserId = $user['id'];
        self::$cachedPermissions = $permissions;

        return $permissions;
    }

    /**
     * Clear permission cache
     */
    public function clearCache(): void
    {
        self::$cachedPermissions = null;
        self::$cachedUserId = null;
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers(?array $user): bool
    {
        return $this->hasPermission($user, 'users.view');
    }

    /**
     * Check if user can edit users
     */
    public function canEditUsers(?array $user): bool
    {
        return $this->hasPermission($user, 'users.edit');
    }

    /**
     * Check if user can ban users
     */
    public function canBanUsers(?array $user): bool
    {
        return $this->hasPermission($user, 'users.ban');
    }

    /**
     * Check if user can adjust balance
     */
    public function canAdjustBalance(?array $user): bool
    {
        return $this->hasPermission($user, 'users.balance');
    }

    /**
     * Check if user can manage deposits
     */
    public function canManageDeposits(?array $user): bool
    {
        return $this->hasPermission($user, 'deposits.view');
    }

    /**
     * Check if user can approve deposits
     */
    public function canApproveDeposits(?array $user): bool
    {
        return $this->hasPermission($user, 'deposits.approve');
    }

    /**
     * Check if user can reject deposits
     */
    public function canRejectDeposits(?array $user): bool
    {
        return $this->hasPermission($user, 'deposits.reject');
    }

    /**
     * Check if user can manage tickets
     */
    public function canManageTickets(?array $user): bool
    {
        return $this->hasPermission($user, 'tickets.view');
    }

    /**
     * Check if user can reply to tickets
     */
    public function canReplyTickets(?array $user): bool
    {
        return $this->hasPermission($user, 'tickets.reply');
    }

    /**
     * Check if user can close tickets
     */
    public function canCloseTickets(?array $user): bool
    {
        return $this->hasPermission($user, 'tickets.close');
    }

    /**
     * Check if user can view settings
     */
    public function canViewSettings(?array $user): bool
    {
        return $this->hasPermission($user, 'settings.view');
    }

    /**
     * Check if user can edit settings
     */
    public function canManageSettings(?array $user): bool
    {
        return $this->hasPermission($user, 'settings.edit');
    }

    /**
     * Check if user can manage plans
     */
    public function canManagePlans(?array $user): bool
    {
        return $this->hasPermission($user, 'plans.manage');
    }

    /**
     * Check if user can manage providers
     */
    public function canManageProviders(?array $user): bool
    {
        return $this->hasPermission($user, 'providers.manage');
    }

    /**
     * Check if user can manage coupons
     */
    public function canManageCoupons(?array $user): bool
    {
        return $this->hasPermission($user, 'coupons.manage');
    }

    /**
     * Check if user can manage roles
     */
    public function canManageRoles(?array $user): bool
    {
        return $this->hasPermission($user, 'roles.manage');
    }

    /**
     * Check if user can impersonate other users
     */
    public function canImpersonate(?array $user): bool
    {
        return $this->hasPermission($user, 'impersonate');
    }

    /**
     * Check if user can view system health
     */
    public function canViewHealth(?array $user): bool
    {
        return $this->hasPermission($user, 'health.view');
    }

    /**
     * Get available permissions list
     */
    public function getAvailablePermissions(): array
    {
        return AdminRole::getAvailablePermissions();
    }

    /**
     * Get user's role
     */
    public function getUserRole(?array $user): ?array
    {
        if (!$user || empty($user['role_id'])) {
            return null;
        }

        return $this->roleModel->getById($user['role_id']);
    }
}
