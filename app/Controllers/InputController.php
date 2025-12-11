<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\RevenueTargetModel;
use App\Models\RevenueRealizationModel;

class InputController extends BaseController
{
    protected $companyModel;
    protected $targetModel;
    protected $realizationModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->targetModel = new RevenueTargetModel();
        $this->realizationModel = new RevenueRealizationModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Input Revenue',
            'companies' => $this->companyModel->getActiveCompanies(),
        ];

        return view('input/index', $data);
    }

    public function store()
    {
        $type = $this->request->getPost('type');
        $companyId = $this->request->getPost('company_id');
        $amount = $this->request->getPost('amount');
        $date = $this->request->getPost('date');

        if (!$companyId || !$amount || !$date) {
            return redirect()->back()->withInput()->with('error', 'Semua field harus diisi.');
        }

        try {
            if ($type === 'realisasi') {
                $this->realizationModel->insert([
                    'company_id' => $companyId,
                    'date' => $date,
                    'amount' => $amount,
                    'description' => 'Input Manual',
                ]);
                $message = 'Realisasi berhasil disimpan.';
            } else {
                $dateObj = new \DateTime($date);
                $year = (int) $dateObj->format('Y');
                $month = (int) $dateObj->format('n');
                
                $this->targetModel->upsertTarget($companyId, $year, $month, $amount);
                $message = 'Target berhasil disimpan.';
            }

            return redirect()->to('/input')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
