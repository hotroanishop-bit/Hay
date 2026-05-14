<?php
/**
 * EmailVerification Model
 * Handles email verification tokens for new users
 */

class EmailVerification extends BaseModel
{
    protected string $table = 'email_verifications';
    
    protected array $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'created_at'
    ];

    /**
     * Create a new email verification token
     * Returns the plain token (stored hashed)
     */
    public function createToken(int $userId): string
    {
        // Delete any existing tokens for this user
        $this->deleteByUser($userId);
        
        // Generate a secure random token
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);
        
        // Set expiration to 24 hours from now
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $this->create([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $plainToken;
    }

    /**
     * Find email verification by token hash
     */
    public function findByToken(string $tokenHash): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE token_hash = :token_hash LIMIT 1";
        $results = $this->query($sql, ['token_hash' => $tokenHash]);
        return $results[0] ?? null;
    }

    /**
     * Find email verification by user ID
     */
    public function findByUser(int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
        $results = $this->query($sql, ['user_id' => $userId]);
        return $results[0] ?? null;
    }

    /**
     * Delete expired tokens
     */
    public function deleteExpired(): int
    {
        $sql = "DELETE FROM {$this->table} WHERE expires_at < NOW()";
        $this->execute($sql);
        
        $stmt = $this->db()->prepare("SELECT ROW_COUNT() as affected");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) ($result['affected'] ?? 0);
    }

    /**
     * Delete tokens by user ID
     */
    public function deleteByUser(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        return $this->execute($sql, ['user_id' => $userId]);
    }

    /**
     * Verify a token and return user_id if valid
     */
    public function verify(string $token): ?int
    {
        $tokenHash = hash('sha256', $token);
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE token_hash = :token_hash 
                AND expires_at > NOW() 
                LIMIT 1";
        
        $results = $this->query($sql, ['token_hash' => $tokenHash]);
        
        if (empty($results)) {
            return null;
        }
        
        $verification = $results[0];
        $userId = (int) $verification['user_id'];
        
        // Delete the used token
        $this->deleteByUser($userId);
        
        return $userId;
    }
}
