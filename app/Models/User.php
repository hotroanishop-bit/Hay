<?php
/**
 * User Model
 * Handles user authentication and account management
 */

class User extends BaseModel
{
    protected string $table = 'users';
    
    protected array $fillable = [
        'email',
        'password_hash',
        'name',
        'balance',
        'is_admin',
        'two_factor_secret',
        'two_factor_enabled',
        'email_verified_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Find a user by email address
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy(['email' => $email]);
    }

    /**
     * Verify user password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Hash a password for storage
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Update user balance
     */
    public function updateBalance(int $userId, float $amount): bool
    {
        $sql = "UPDATE {$this->table} SET balance = balance + :amount, updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['amount' => $amount, 'id' => $userId]);
    }

    /**
     * Enable two-factor authentication
     */
    public function enable2FA(int $userId, string $secret): bool
    {
        $sql = "UPDATE {$this->table} SET two_factor_secret = :secret, two_factor_enabled = 1, updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['secret' => $secret, 'id' => $userId]);
    }

    /**
     * Disable two-factor authentication
     */
    public function disable2FA(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET two_factor_secret = NULL, two_factor_enabled = 0, updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['id' => $userId]);
    }

    /**
     * Mark email as verified
     */
    public function verifyEmail(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET email_verified_at = NOW(), updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['id' => $userId]);
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified(int $userId): bool
    {
        $user = $this->find($userId);
        return $user && $user['email_verified_at'] !== null;
    }

    /**
     * Create a new user with hashed password
     */
    public function createUser(array $data): int
    {
        if (isset($data['password'])) {
            $data['password_hash'] = $this->hashPassword($data['password']);
            unset($data['password']);
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    /**
     * Get user's current balance
     */
    public function getBalance(int $userId): float
    {
        $user = $this->find($userId);
        return $user ? (float) $user['balance'] : 0.0;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(int $userId): bool
    {
        $user = $this->find($userId);
        return $user && (bool) $user['is_admin'];
    }
}
