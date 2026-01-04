<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function index()
    {
        // Bersihkan output buffer untuk mencegah output tidak diinginkan
        if (ob_get_length()) {
            ob_clean();
        }
        
        return view('pages/login');
    }

    public function login()
    {
        $session = session();
        $model = new \App\Models\UserModel();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        
        $user = $model->where('email', $email)->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $ses_data = [
                    'user_id'       => $user['id'],
                    'user_email'    => $user['email'],
                    'logged_in'     => TRUE
                ];
                $session->set($ses_data);
                return redirect()->to('/dashboard');
            }
        }
        $session->setFlashdata('msg', 'Email atau Password Salah');
        return redirect()->to('/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
    
    // HAPUS METHOD DAN KURUNG TAMBAHAN DI BAWAH INI
}