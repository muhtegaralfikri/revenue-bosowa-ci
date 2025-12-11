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
        // Validation rules
        $rules = [
            'type' => [
                'rules' => 'required|in_list[realisasi,target]',
                'errors' => [
                    'required' => 'Jenis harus dipilih.',
                    'in_list' => 'Jenis tidak valid.',
                ]
            ],
            'company_id' => [
                'rules' => 'required|integer|is_not_unique[companies.id]',
                'errors' => [
                    'required' => 'Entity harus dipilih.',
                    'integer' => 'Entity tidak valid.',
                    'is_not_unique' => 'Entity tidak ditemukan.',
                ]
            ],
            'amount' => [
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required' => 'Revenue harus diisi.',
                    'numeric' => 'Revenue harus berupa angka.',
                    'greater_than' => 'Revenue harus lebih dari 0.',
                ]
            ],
            'date' => [
                'rules' => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required' => 'Tanggal harus diisi.',
                    'valid_date' => 'Format tanggal tidak valid.',
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $type = $this->request->getPost('type');
        $companyId = (int) $this->request->getPost('company_id');
        $amount = (float) $this->request->getPost('amount');
        $date = $this->request->getPost('date');

        try {
            if ($type === 'realisasi') {
                $this->realizationModel->insert([
                    'company_id' => $companyId,
                    'date' => $date,
                    'amount' => $amount,
                    'description' => 'Input Manual',
                ]);
                
                // Clear cache after input
                $this->realizationModel->clearCache();
                $message = 'Realisasi berhasil disimpan.';
            } else {
                $dateObj = new \DateTime($date);
                $year = (int) $dateObj->format('Y');
                $month = (int) $dateObj->format('n');
                
                $this->targetModel->upsertTarget($companyId, $year, $month, $amount);
                $message = 'Target berhasil disimpan.';
            }

            log_message('info', "Revenue input: type={$type}, company={$companyId}, amount={$amount}, date={$date}");
            return redirect()->to('/input')->with('success', $message);
        } catch (\Exception $e) {
            log_message('error', 'Revenue input error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
