<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ApparatusModel;

class ApparatusController extends BaseController
{
    public function index()
    {
        $apparatusModel = new ApparatusModel();
        $data = [
            'title'       => 'Manajemen Aparatur Desa',
            'apparatuses' => $apparatusModel->orderBy('id', 'DESC')->findAll()
        ];
        return view('dashboard/apparatus/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Aparatur Desa'
        ];
        return view('dashboard/apparatus/form', $data);
    }

    public function store()
    {
        // Aturan validasi
        $rules = [
            'name'     => 'required|min_length[3]',
            'position' => 'required',
            'photo'    => 'uploaded[photo]|max_size[photo,2048]|is_image[photo]|mime_in[photo,image/jpg,image/jpeg,image/png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $apparatusModel = new ApparatusModel();
        $photoFile = $this->request->getFile('photo');
        
        $photoName = 'default.jpg';
        if ($photoFile->isValid() && !$photoFile->hasMoved()) {
            $photoName = $photoFile->getRandomName();
            $photoFile->move('uploads/apparatus', $photoName);
        }

        $apparatusModel->save([
            'name'     => $this->request->getPost('name'),
            'position' => $this->request->getPost('position'),
            'photo'    => $photoName
        ]);

        return redirect()->to('/dashboard/apparatus')->with('success', 'Data berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $apparatusModel = new ApparatusModel();
        $apparatus = $apparatusModel->find($id);

        if ($apparatus) {
            // Hapus file gambar jika bukan default
            if ($apparatus['photo'] != 'default.jpg') {
                $filePath = FCPATH . 'uploads/apparatus/' . $apparatus['photo'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $apparatusModel->delete($id);
            return redirect()->to('/dashboard/apparatus')->with('success', 'Data berhasil dihapus.');
        }

        return redirect()->to('/dashboard/apparatus')->with('error', 'Data tidak ditemukan.');
    }
    // Di controller manapun
}