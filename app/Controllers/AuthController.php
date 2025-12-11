<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LoginAttemptModel;

class AuthController extends BaseController
{
    protected $loginAttempts;

    public function __construct()
    {
        $this->loginAttempts = new LoginAttemptModel();
    }

    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $ip = $this->request->getIPAddress();
        $email = $this->request->getPost('email');

        // Check if blocked
        if ($this->loginAttempts->isBlocked($ip, $email)) {
            $remaining = $this->loginAttempts->getRemainingLockout($ip, $email);
            $minutes = ceil($remaining / 60);
            return redirect()->back()->withInput()
                ->with('error', "Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit.");
        }

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $user = $userModel->verifyPassword($email, $this->request->getPost('password'));

        if ($user) {
            // Clear login attempts on success
            $this->loginAttempts->clearAttempts($ip, $email);

            session()->set([
                'user_id' => $user['id'],
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'user_role' => $user['role'],
                'logged_in' => true,
            ]);

            log_message('info', "User logged in: {$email} from IP: {$ip}");
            return redirect()->to('/dashboard')->with('success', 'Login berhasil!');
        }

        // Record failed attempt
        $this->loginAttempts->recordFailedAttempt($ip, $email);
        $remaining = $this->loginAttempts->getRemainingAttempts($ip, $email);

        log_message('warning', "Failed login attempt for: {$email} from IP: {$ip}");

        $message = 'Email atau password salah.';
        if ($remaining > 0 && $remaining <= 3) {
            $message .= " Sisa {$remaining} percobaan.";
        }

        return redirect()->back()->withInput()->with('error', $message);
    }

    public function logout()
    {
        $email = session()->get('user_email');
        session()->destroy();
        
        log_message('info', "User logged out: {$email}");
        return redirect()->to('/login')->with('success', 'Logout berhasil.');
    }
}
