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

    public function getRealizationsByYearMonth($year, $month)
    {
        return $this->select('company_id, SUM(amount) as total_amount')
            ->where('YEAR(date)', $year)
            ->where('MONTH(date)', $month)
            ->groupBy('company_id')
            ->findAll();
    }

    public function getRealizationsByYear($year)
    {
        return $this->select('company_id, MONTH(date) as month, SUM(amount) as total_amount')
            ->where('YEAR(date)', $year)
            ->groupBy('company_id')
            ->groupBy('MONTH(date)')
            ->findAll();
    }

    public function getDailyRealizations($year, $month)
    {
        return $this->select('revenue_realizations.*, companies.code, companies.name as company_name')
            ->join('companies', 'companies.id = revenue_realizations.company_id')
            ->where('YEAR(date)', $year)
            ->where('MONTH(date)', $month)
            ->orderBy('date', 'ASC')
            ->findAll();
    }

    public function getMonthlyTotalsByCompany($year)
    {
        $results = $this->select('company_id, MONTH(date) as month, SUM(amount) as total')
            ->where('YEAR(date)', $year)
            ->groupBy('company_id')
            ->groupBy('MONTH(date)')
            ->findAll();

        $data = [];
        foreach ($results as $row) {
            $data[$row['company_id']][$row['month']] = $row['total'];
        }
        return $data;
    }
}
