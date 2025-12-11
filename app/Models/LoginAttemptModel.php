<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table = 'login_attempts';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ip_address', 'email', 'attempts', 'last_attempt', 'blocked_until'];
    protected $useTimestamps = false;

    // Configuration
    protected $maxAttempts = 5;
    protected $lockoutMinutes = 15;
    protected $attemptWindow = 15; // minutes

    /**
     * Check if IP/email is blocked
     */
    public function isBlocked(string $ip, ?string $email = null): bool
    {
        $record = $this->getAttemptRecord($ip, $email);
        
        if (!$record) {
            return false;
        }

        // Check if blocked
        if ($record['blocked_until'] && strtotime($record['blocked_until']) > time()) {
            return true;
        }

        return false;
    }

    /**
     * Get remaining lockout time in seconds
     */
    public function getRemainingLockout(string $ip, ?string $email = null): int
    {
        $record = $this->getAttemptRecord($ip, $email);
        
        if (!$record || !$record['blocked_until']) {
            return 0;
        }

        $remaining = strtotime($record['blocked_until']) - time();
        return max(0, $remaining);
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt(string $ip, ?string $email = null): void
    {
        $record = $this->getAttemptRecord($ip, $email);
        $now = date('Y-m-d H:i:s');

        if (!$record) {
            // First attempt
            $this->insert([
                'ip_address' => $ip,
                'email' => $email,
                'attempts' => 1,
                'last_attempt' => $now,
            ]);
        } else {
            // Check if attempt window has passed (reset counter)
            $windowStart = strtotime("-{$this->attemptWindow} minutes");
            
            if (strtotime($record['last_attempt']) < $windowStart) {
                // Reset attempts
                $this->update($record['id'], [
                    'attempts' => 1,
                    'last_attempt' => $now,
                    'blocked_until' => null,
                ]);
            } else {
                // Increment attempts
                $newAttempts = $record['attempts'] + 1;
                $blockedUntil = null;

                if ($newAttempts >= $this->maxAttempts) {
                    $blockedUntil = date('Y-m-d H:i:s', strtotime("+{$this->lockoutMinutes} minutes"));
                    log_message('warning', "Login blocked for IP: {$ip}, Email: {$email}");
                }

                $this->update($record['id'], [
                    'attempts' => $newAttempts,
                    'last_attempt' => $now,
                    'blocked_until' => $blockedUntil,
                ]);
            }
        }
    }

    /**
     * Clear attempts after successful login
     */
    public function clearAttempts(string $ip, ?string $email = null): void
    {
        $this->where('ip_address', $ip);
        
        if ($email) {
            $this->orWhere('email', $email);
        }
        
        $this->delete();
    }

    /**
     * Get attempt record by IP or email
     */
    protected function getAttemptRecord(string $ip, ?string $email = null): ?array
    {
        $builder = $this->builder();
        $builder->groupStart();
        $builder->where('ip_address', $ip);
        
        if ($email) {
            $builder->orWhere('email', $email);
        }
        
        $builder->groupEnd();
        
        return $builder->get()->getRowArray();
    }

    /**
     * Get remaining attempts
     */
    public function getRemainingAttempts(string $ip, ?string $email = null): int
    {
        $record = $this->getAttemptRecord($ip, $email);
        
        if (!$record) {
            return $this->maxAttempts;
        }

        // Check if attempt window has passed
        $windowStart = strtotime("-{$this->attemptWindow} minutes");
        if (strtotime($record['last_attempt']) < $windowStart) {
            return $this->maxAttempts;
        }

        return max(0, $this->maxAttempts - $record['attempts']);
    }

    /**
     * Clean old records (for cron job)
     */
    public function cleanOldRecords(): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-24 hours'));
        
        return $this->where('last_attempt <', $cutoff)
            ->where('blocked_until IS NULL')
            ->orWhere('blocked_until <', date('Y-m-d H:i:s'))
            ->delete();
    }
}
