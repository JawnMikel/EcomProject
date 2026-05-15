<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class TwoFactorService
{
    private const CODE_LENGTH = 6;
    private const EXPIRY_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    public function __construct(private PDO $db) {}

    /**
     * Generate and store a 2FA code for a user
     */
    public function generateAndStoreCode(int $userId): string
    {
        $code = $this->generateCode();
        $expiresAt = (new \DateTime())->add(new \DateInterval('PT' . self::EXPIRY_MINUTES . 'M'))->format('Y-m-d H:i:s');

        // Invalidate previous codes
        $stmt = $this->db->prepare('UPDATE two_factor_codes SET used = 1 WHERE user_id = ? AND used = 0');
        $stmt->execute([$userId]);

        // Insert new code
        $stmt = $this->db->prepare(
            'INSERT INTO two_factor_codes (user_id, code, expires_at, max_attempts) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $code, $expiresAt, self::MAX_ATTEMPTS]);

        return $code;
    }

    /**
     * Verify a 2FA code for a user
     */
    public function verifyCode(int $userId, string $code): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id, attempts, max_attempts, expires_at FROM two_factor_codes 
             WHERE user_id = ? AND code = ? AND used = 0 AND expires_at > NOW() 
             LIMIT 1'
        );
        $stmt->execute([$userId, $code]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            // Mark failed attempt
            $this->recordFailedAttempt($userId);
            return false;
        }

        // Check attempts
        if ($result['attempts'] >= $result['max_attempts']) {
            return false;
        }

        // Mark code as used
        $updateStmt = $this->db->prepare('UPDATE two_factor_codes SET used = 1 WHERE id = ?');
        $updateStmt->execute([$result['id']]);

        return true;
    }

    /**
     * Check if a user has exceeded max 2FA attempts
     */
    public function isLockedOut(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as count FROM two_factor_codes 
             WHERE user_id = ? AND attempts >= max_attempts AND used = 0 AND expires_at > NOW()'
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    /**
     * Record a failed verification attempt
     */
    private function recordFailedAttempt(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE two_factor_codes SET attempts = attempts + 1 
             WHERE user_id = ? AND used = 0 AND expires_at > NOW() 
             ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$userId]);
    }

    /**
     * Generate a random 6-digit code
     */
    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Clean up expired codes
     */
    public function cleanupExpiredCodes(): void
    {
        $stmt = $this->db->prepare('DELETE FROM two_factor_codes WHERE expires_at < NOW()');
        $stmt->execute();
    }

    /**
     * Get remaining attempts for current code
     */
    public function getRemainingAttempts(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT max_attempts - COALESCE(attempts, 0) as remaining FROM two_factor_codes 
             WHERE user_id = ? AND used = 0 AND expires_at > NOW() 
             ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ? max(0, (int) $result['remaining']) : 0;
    }
}
