<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Kelola Pengguna',
            'users' => $this->userModel->where('is_active', 1)->findAll(),
        ];

        return view('users/index', $data);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email|is_unique[ci_users.email]',
            'password' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        // Validate password strength
        $password = $this->request->getPost('password');
        $passwordErrors = UserModel::validatePasswordStrength($password);
        if (!empty($passwordErrors)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $passwordErrors));
        }

        $this->userModel->insert([
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'viewer',
            'is_active' => 1,
        ]);

        log_message('info', 'New user created: ' . $this->request->getPost('email'));
        return redirect()->to('/users')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|min_length[3]',
            'email' => "required|valid_email|is_unique[ci_users.email,id,{$id}]",
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $passwordErrors = UserModel::validatePasswordStrength($password);
            if (!empty($passwordErrors)) {
                return redirect()->back()->withInput()->with('error', implode('<br>', $passwordErrors));
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);

        log_message('info', 'User updated: ' . $this->request->getPost('email'));
        return redirect()->to('/users')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function delete($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        // Prevent deleting own account
        if ($user['id'] == session()->get('user_id')) {
            return redirect()->to('/users')->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $this->userModel->delete($id);

        return redirect()->to('/users')->with('success', 'Pengguna berhasil dihapus.');
    }
}
