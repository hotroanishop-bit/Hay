<?php
/**
 * AdminRole Model
 * Handles admin role management with permissions
 */

class AdminRole extends BaseModel
{
    protected string $table = 'admin_roles';
    protected array $fillable = [
        'name',
        'description',
        'permissions',
        'is_system'
    ];

    /**
     * Get all roles
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY is_system DESC, name ASC";
        return $this->query($sql, []);
    }

    /**
     * Get role by ID
     */
    public function getById(int $id): ?array
    {
        return $this->find($id);
    }

    /**
     * Get role by name
     */
    public function getByName(string $name): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = :name LIMIT 1";
        $results = $this->query($sql, ['name' => $name]);
        return $results[0] ?? null;
    }

    /**
     * Create a new role
     */
    public function createRole(array $data): int
    {
        // Ensure permissions is JSON encoded
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $data['permissions'] = json_encode($data['permissions']);
        }
        
        return $this->create($data);
    }

    /**
     * Update a role
     */
    public function updateRole(int $id, array $data): bool
    {
        // Ensure permissions is JSON encoded
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $data['permissions'] = json_encode($data['permissions']);
        }
        
        return $this->update($id, $data);
    }

    /**
     * Delete a role (only non-system roles)
     */
    public function deleteRole(int $id): bool
    {
        $role = $this->find($id);
        
        if (!$role || $role['is_system']) {
            return false;
        }
        
        // Set users with this role to null
        $sql = "UPDATE users SET role_id = NULL WHERE role_id = :role_id";
        $this->execute($sql, ['role_id' => $id]);
        
        return $this->delete($id);
    }

    /**
     * Get permissions for a role
     */
    public function getPermissions(int $id): array
    {
        $role = $this->find($id);
        
        if (!$role) {
            return [];
        }
        
        $permissions = $role['permissions'] ?? '[]';
        
        if (is_string($permissions)) {
            return json_decode($permissions, true) ?? [];
        }
        
        return is_array($permissions) ? $permissions : [];
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(int $roleId, string $permission): bool
    {
        $permissions = $this->getPermissions($roleId);
        return in_array($permission, $permissions);
    }

    /**
     * Get users count for each role
     */
    public function getUserCounts(): array
    {
        $sql = "SELECT r.id, r.name, COUNT(u.id) as user_count 
                FROM {$this->table} r 
                LEFT JOIN users u ON r.id = u.role_id 
                GROUP BY r.id, r.name";
        
        $results = $this->query($sql, []);
        $counts = [];
        
        foreach ($results as $row) {
            $counts[$row['id']] = (int) $row['user_count'];
        }
        
        return $counts;
    }

    /**
     * Get all available permission keys
     */
    public static function getAvailablePermissions(): array
    {
        return [
            'users' => [
                'users.view' => 'View users list and details',
                'users.edit' => 'Edit user information',
                'users.ban' => 'Ban/unban users',
                'users.balance' => 'Adjust user balance'
            ],
            'deposits' => [
                'deposits.view' => 'View deposits list',
                'deposits.approve' => 'Approve deposits',
                'deposits.reject' => 'Reject deposits'
            ],
            'tickets' => [
                'tickets.view' => 'View support tickets',
                'tickets.reply' => 'Reply to tickets',
                'tickets.close' => 'Close tickets'
            ],
            'settings' => [
                'settings.view' => 'View system settings',
                'settings.edit' => 'Edit system settings'
            ],
            'content' => [
                'plans.manage' => 'Manage subscription plans',
                'providers.manage' => 'Manage API providers',
                'coupons.manage' => 'Manage coupons'
            ],
            'system' => [
                'roles.manage' => 'Manage admin roles',
                'impersonate' => 'Impersonate users',
                'health.view' => 'View system health'
            ]
        ];
    }

    /**
     * Get flat list of all permission keys
     */
    public static function getAllPermissionKeys(): array
    {
        $keys = [];
        foreach (self::getAvailablePermissions() as $group => $permissions) {
            $keys = array_merge($keys, array_keys($permissions));
        }
        return $keys;
    }
}
