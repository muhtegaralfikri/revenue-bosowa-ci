<?php

namespace App\Models;

use CodeIgniter\Model;

class RevenueRealizationModel extends Model
{
    protected $table = 'revenue_realizations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['company_id', 'date', 'amount', 'description'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $cache;
    protected $cacheTTL = 300; // 5 minutes
    
    public function __construct()
    {
        parent::__construct();
        $this->cache = \Config\Services::cache();
    }

    public function getRealizationsByYearMonth($year, $month)
    {
        $cacheKey = "realizations_ym_{$year}_{$month}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $result = $this->select('company_id, SUM(amount) as total_amount')
            ->where('YEAR(date)', $year)
            ->where('MONTH(date)', $month)
            ->groupBy('company_id')
            ->findAll();
            
        $this->cache->save($cacheKey, $result, $this->cacheTTL);
        return $result;
    }

    public function getRealizationsByYear($year)
    {
        $cacheKey = "realizations_year_{$year}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $result = $this->select('company_id, MONTH(date) as month, SUM(amount) as total_amount')
            ->where('YEAR(date)', $year)
            ->groupBy('company_id')
            ->groupBy('MONTH(date)')
            ->findAll();
            
        $this->cache->save($cacheKey, $result, $this->cacheTTL);
        return $result;
    }

    public function getDailyRealizations($year, $month)
    {
        $cacheKey = "daily_realizations_{$year}_{$month}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $result = $this->select('revenue_realizations.*, companies.code, companies.name as company_name')
            ->join('companies', 'companies.id = revenue_realizations.company_id')
            ->where('YEAR(date)', $year)
            ->where('MONTH(date)', $month)
            ->orderBy('date', 'ASC')
            ->findAll();
            
        $this->cache->save($cacheKey, $result, $this->cacheTTL);
        return $result;
    }

    public function getMonthlyTotalsByCompany($year)
    {
        $cacheKey = "monthly_totals_{$year}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $results = $this->select('company_id, MONTH(date) as month, SUM(amount) as total')
            ->where('YEAR(date)', $year)
            ->groupBy('company_id')
            ->groupBy('MONTH(date)')
            ->findAll();

        $data = [];
        foreach ($results as $row) {
            $data[$row['company_id']][$row['month']] = $row['total'];
        }
        
        $this->cache->save($cacheKey, $data, $this->cacheTTL);
        return $data;
    }
    
    /**
     * Clear all realization caches
     */
    public function clearCache(): void
    {
        $year = date('Y');
        $this->cache->delete("realizations_year_{$year}");
        $this->cache->delete("monthly_totals_{$year}");
        
        // Clear monthly caches for current year
        for ($m = 1; $m <= 12; $m++) {
            $this->cache->delete("realizations_ym_{$year}_{$m}");
            $this->cache->delete("daily_realizations_{$year}_{$m}");
        }
    }
}
