<?php
/**
 * PasswordReset Model
 * Handles password reset tokens for forgot password flow
 */

class PasswordReset extends BaseModel
{
    protected string $table = 'password_resets';
    
    protected array $fillable = [
        'email',
        'token_hash',
        'expires_at',
        'created_at'
    ];

    /**
     * Create a new password reset token
     * Returns the plain token (stored hashed)
     */
    public function createToken(string $email): string
    {
        // Delete any existing tokens for this email
        $this->deleteByEmail($email);
        
        // Generate a secure random token
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);
        
        // Set expiration to 1 hour from now
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->create([
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $plainToken;
    }

    /**
     * Find password reset by token hash
     */
    public function findByToken(string $tokenHash): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE token_hash = :token_hash LIMIT 1";
        $results = $this->query($sql, ['token_hash' => $tokenHash]);
        return $results[0] ?? null;
    }

    /**
     * Find password reset by email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email ORDER BY created_at DESC LIMIT 1";
        $results = $this->query($sql, ['email' => $email]);
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
     * Delete tokens by email
     */
    public function deleteByEmail(string $email): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE email = :email";
        return $this->execute($sql, ['email' => $email]);
    }

    /**
     * Check if a token is valid for an email
     */
    public function isValidToken(string $email, string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE email = :email 
                AND token_hash = :token_hash 
                AND expires_at > NOW() 
                LIMIT 1";
        
        $results = $this->query($sql, [
            'email' => $email,
            'token_hash' => $tokenHash
        ]);
        
        return !empty($results);
    }
}
