<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\RevenueTargetModel;

class TargetController extends BaseController
{
    protected $companyModel;
    protected $targetModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->targetModel = new RevenueTargetModel();
    }

    public function index()
    {
        $year = $this->request->getGet('year') ?? date('Y');
        
        $data = [
            'title' => 'Target Revenue',
            'year' => $year,
            'years' => range(date('Y') - 2, date('Y') + 1),
            'companies' => $this->companyModel->getActiveCompanies(),
            'targets' => $this->targetModel->getTargetsByYear($year),
            'months' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
        ];

        return view('targets/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Target',
            'companies' => $this->companyModel->getActiveCompanies(),
            'years' => range(date('Y') - 1, date('Y') + 2),
            'months' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
        ];

        return view('targets/create', $data);
    }

    public function store()
    {
        $rules = [
            'company_id' => 'required|numeric',
            'year' => 'required|numeric',
            'month' => 'required|numeric|greater_than[0]|less_than[13]',
            'target_amount' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->targetModel->upsertTarget(
            $this->request->getPost('company_id'),
            $this->request->getPost('year'),
            $this->request->getPost('month'),
            $this->request->getPost('target_amount')
        );

        return redirect()->to('/targets')->with('success', 'Target berhasil disimpan.');
    }

    public function edit($id)
    {
        $target = $this->targetModel->find($id);
        
        if (!$target) {
            return redirect()->to('/targets')->with('error', 'Target tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Target',
            'target' => $target,
            'companies' => $this->companyModel->getActiveCompanies(),
            'years' => range(date('Y') - 1, date('Y') + 2),
            'months' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
        ];

        return view('targets/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'target_amount' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->targetModel->update($id, [
            'target_amount' => $this->request->getPost('target_amount'),
        ]);

        return redirect()->to('/targets')->with('success', 'Target berhasil diupdate.');
    }

    public function delete($id)
    {
        $this->targetModel->delete($id);
        return redirect()->to('/targets')->with('success', 'Target berhasil dihapus.');
    }
}
