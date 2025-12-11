<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\RevenueRealizationModel;

class RealizationController extends BaseController
{
    protected $companyModel;
    protected $realizationModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->realizationModel = new RevenueRealizationModel();
    }

    public function index()
    {
        $year = $this->request->getGet('year') ?? date('Y');
        $month = $this->request->getGet('month') ?? date('n');
        
        $data = [
            'title' => 'Realisasi Revenue',
            'year' => $year,
            'month' => $month,
            'years' => range(date('Y') - 2, date('Y') + 1),
            'companies' => $this->companyModel->getActiveCompanies(),
            'realizations' => $this->realizationModel->getDailyRealizations($year, $month),
            'months' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
        ];

        return view('realizations/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Realisasi',
            'companies' => $this->companyModel->getActiveCompanies(),
        ];

        return view('realizations/create', $data);
    }

    public function store()
    {
        $rules = [
            'company_id' => 'required|numeric',
            'date' => 'required|valid_date',
            'amount' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->realizationModel->insert([
            'company_id' => $this->request->getPost('company_id'),
            'date' => $this->request->getPost('date'),
            'amount' => $this->request->getPost('amount'),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('/realizations')->with('success', 'Realisasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $realization = $this->realizationModel->find($id);
        
        if (!$realization) {
            return redirect()->to('/realizations')->with('error', 'Realisasi tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Realisasi',
            'realization' => $realization,
            'companies' => $this->companyModel->getActiveCompanies(),
        ];

        return view('realizations/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'company_id' => 'required|numeric',
            'date' => 'required|valid_date',
            'amount' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->realizationModel->update($id, [
            'company_id' => $this->request->getPost('company_id'),
            'date' => $this->request->getPost('date'),
            'amount' => $this->request->getPost('amount'),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('/realizations')->with('success', 'Realisasi berhasil diupdate.');
    }

    public function delete($id)
    {
        $this->realizationModel->delete($id);
        return redirect()->to('/realizations')->with('success', 'Realisasi berhasil dihapus.');
    }
}
