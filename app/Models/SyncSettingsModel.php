<?php

namespace App\Models;

use CodeIgniter\Model;

class SyncSettingsModel extends Model
{
    protected $table = 'sync_settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['key', 'value'];
    protected $useTimestamps = true;

    public function getValue(string $key, $default = null)
    {
        $result = $this->where('key', $key)->first();
        return $result ? $result['value'] : $default;
    }

    public function setValue(string $key, $value): bool
    {
        $existing = $this->where('key', $key)->first();
        
        if ($existing) {
            return $this->update($existing['id'], ['value' => $value]);
        }
        
        return $this->insert(['key' => $key, 'value' => $value]) !== false;
    }

    public function getLastSyncTime(): ?string
    {
        return $this->getValue('last_sync_time');
    }

    public function setLastSyncTime(string $time): bool
    {
        return $this->setValue('last_sync_time', $time);
    }

    public function getLastSyncStatus(): string
    {
        return $this->getValue('last_sync_status', 'never');
    }

    public function setLastSyncStatus(string $status): bool
    {
        return $this->setValue('last_sync_status', $status);
    }

    public function getSyncInterval(): int
    {
        return (int) $this->getValue('sync_interval_minutes', 5);
    }

    public function canSync(): bool
    {
        $lastSync = $this->getLastSyncTime();
        if (!$lastSync) {
            return true;
        }

        $interval = $this->getSyncInterval();
        $lastSyncTime = strtotime($lastSync);
        // Toleransi 30 detik untuk menghindari race condition dengan cron
        $tolerance = 30;
        $nextSyncTime = $lastSyncTime + ($interval * 60) - $tolerance;

        return time() >= $nextSyncTime;
    }

    public function updateSyncStats(string $status, int $count): void
    {
        $this->setLastSyncTime(date('Y-m-d H:i:s'));
        $this->setLastSyncStatus($status);
        $this->setValue('last_sync_count', (string) $count);
    }
}
