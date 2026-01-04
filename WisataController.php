<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\WisataModel;

class WisataController extends BaseController
{
    protected $wisataModel;

    public function __construct()
    {
        $this->wisataModel = new WisataModel();
    }

    // Halaman Index untuk Dashboard
    public function index()
    {
        $data = [
            'title' => 'Data Wisata Desa',
            'wisata' => $this->wisataModel->findAll()
        ];
        return view('dashboard/wisata/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Data Wisata',
            'validation' => \Config\Services::validation()
        ];
        return view('dashboard/wisata/create', $data);
    }

    public function store()
    {
        if (!$this->validate([
            'nama'      => 'required',
            'lokasi'    => 'required',
            'deskripsi' => 'required',
            'gambar'    => [
                'rules'  => 'is_image[gambar]|max_size[gambar,2048]|mime_in[gambar,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'is_image' => 'File harus berupa gambar.',
                    'max_size' => 'Ukuran gambar maksimal 2MB.',
                    'mime_in'  => 'Format gambar harus JPG, JPEG, atau PNG.'
                ]
            ]
        ])) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $gambar     = $this->request->getFile('gambar');
        $namaGambar = null;
        if ($gambar && $gambar->isValid() && !$gambar->hasMoved()) {
            $namaGambar = $gambar->getRandomName();
            $gambar->move('uploads/wisata', $namaGambar);
        }

        $this->wisataModel->save([
            'nama'      => $this->request->getPost('nama'),
            'lokasi'    => $this->request->getPost('lokasi'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'gambar'    => $namaGambar,
        ]);

        return redirect()->to(base_url('dashboard/wisata'))->with('success', 'Data wisata berhasil ditambahkan');
    }

    public function edit($id)
    {
        $data = [
            'title'      => 'Edit Data Wisata',
            'validation' => \Config\Services::validation(),
            'wisata'     => $this->wisataModel->find($id)
        ];
        return view('dashboard/wisata/edit', $data);
    }

    public function update($id)
    {
        if (!$this->validate([
            'nama'      => 'required',
            'lokasi'    => 'required',
            'deskripsi' => 'required',
            'gambar'    => [
                'rules' => 'if_exist|is_image[gambar]|max_size[gambar,2048]|mime_in[gambar,image/jpg,image/jpeg,image/png]'
            ]
        ])) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $wisata     = $this->wisataModel->find($id);
        $gambar     = $this->request->getFile('gambar');
        $namaGambar = $wisata['gambar'];

        if ($gambar && $gambar->isValid() && !$gambar->hasMoved()) {
            if ($namaGambar && file_exists("uploads/wisata/" . $namaGambar)) {
                unlink("uploads/wisata/" . $namaGambar);
            }
            $namaGambar = $gambar->getRandomName();
            $gambar->move('uploads/wisata', $namaGambar);
        }

        $this->wisataModel->update($id, [
            'nama'      => $this->request->getPost('nama'),
            'lokasi'    => $this->request->getPost('lokasi'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'gambar'    => $namaGambar,
        ]);

        return redirect()->to(base_url('dashboard/wisata'))->with('success', 'Data wisata berhasil diperbarui');
    }

    public function delete($id)
    {
        $wisata = $this->wisataModel->find($id);
        if ($wisata && $wisata['gambar'] && file_exists("uploads/wisata/" . $wisata['gambar'])) {
            unlink("uploads/wisata/" . $wisata['gambar']);
        }
        $this->wisataModel->delete($id);

        return redirect()->to(base_url('dashboard/wisata'))->with('success', 'Data wisata berhasil dihapus');
    }

    public function statistik()
    {
        $data = [
            'title'  => 'Statistik Wisata',
            'wisata' => $this->wisataModel->findAll()
        ];
        return view('dashboard/wisata/statistik', $data);
    }

    // Untuk Landing Page
   public function landing()
    {
        $data = [
            'title'  => 'Wisata Desa Kaliboja',
            'wisata' => $this->wisataModel->findAll()
        ];
        return view('pages/landing', $data);
    }

    // Detail Wisata di Landing Page - DIPERBAIKI
    public function detail($id)
    {
        $wisata = $this->wisataModel->find($id);
        if (!$wisata) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Wisata dengan ID $id tidak ditemukan");
        }

        $data = [
            'title'  => $wisata['nama'] . ' - Wisata Desa Kaliboja',
            'wisata' => $wisata
        ];
        return view('pages/wisata_detail', $data);
    }
    // Di controller manapun
}
