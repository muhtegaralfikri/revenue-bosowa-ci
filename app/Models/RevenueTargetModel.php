<?php

namespace App\Models;

use CodeIgniter\Model;

class RevenueTargetModel extends Model
{
    protected $table = 'revenue_targets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['company_id', 'year', 'month', 'target_amount'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getTargetsByYearMonth($year, $month)
    {
        return $this->select('revenue_targets.*, companies.code, companies.name as company_name')
            ->join('companies', 'companies.id = revenue_targets.company_id')
            ->where('year', $year)
            ->where('month', $month)
            ->findAll();
    }

    public function getTargetsByYear($year)
    {
        return $this->select('revenue_targets.*, companies.code, companies.name as company_name')
            ->join('companies', 'companies.id = revenue_targets.company_id')
            ->where('year', $year)
            ->orderBy('month', 'ASC')
            ->orderBy('companies.code', 'ASC')
            ->findAll();
    }

    public function upsertTarget($companyId, $year, $month, $amount)
    {
        $existing = $this->where([
            'company_id' => $companyId,
            'year' => $year,
            'month' => $month,
        ])->first();

        if ($existing) {
            return $this->update($existing['id'], ['target_amount' => $amount]);
        } else {
            return $this->insert([
                'company_id' => $companyId,
                'year' => $year,
                'month' => $month,
                'target_amount' => $amount,
            ]);
        }
    }
}
