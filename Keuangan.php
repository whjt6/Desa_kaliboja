<?php
namespace App\Controllers;

use App\Models\KeuanganModel;

class Keuangan extends BaseController
{
    protected $keuanganModel;

    public function __construct()
    {
        $this->keuanganModel = new KeuanganModel();
    }

    // 📌 Halaman index + statistik pemasukan/pengeluaran
    public function index()
    {
        $dataKeuangan = $this->keuanganModel->findAll();

        $totalPemasukan  = array_sum(array_column($dataKeuangan, 'pemasukan'));
        $totalPengeluaran = array_sum(array_column($dataKeuangan, 'pengeluaran'));
        $saldo           = $totalPemasukan - $totalPengeluaran;

        $data = [
            'title'    => 'Data Keuangan Desa',
            'keuangan' => $dataKeuangan,
            'stats'    => [
                'pemasukan_total'   => $totalPemasukan,
                'pengeluaran_total' => $totalPengeluaran,
                'saldo'             => $saldo,
            ]
        ];

        return view('dashboard/keuangan/index', $data);
    }

    // 📌 Form create
    public function create()
    {
        $data = [
            'title'      => 'Tambah Data Keuangan',
            'validation' => \Config\Services::validation()
        ];
        return view('dashboard/keuangan/create', $data);
    }

    // 📌 Simpan data baru
    public function store()
    {
        if (!$this->validate([
            'tanggal'     => 'required',
            'keterangan'  => 'required',
            'pemasukan'   => 'permit_empty|numeric',
            'pengeluaran' => 'permit_empty|numeric'
        ])) {
            return redirect()->back()->withInput();
        }

        $this->keuanganModel->insert([
            'tanggal'     => $this->request->getPost('tanggal'),
            'keterangan'  => $this->request->getPost('keterangan'),
            'pemasukan'   => $this->request->getPost('pemasukan') ?: 0,
            'pengeluaran' => $this->request->getPost('pengeluaran') ?: 0,
        ]);

        return redirect()->to('dashboard/keuangan')->with('success', 'Data berhasil ditambahkan');
    }

    // 📌 Form edit
    public function edit($id)
    {
        $keuangan = $this->keuanganModel->find($id);

        if (!$keuangan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Data keuangan dengan ID $id tidak ditemukan");
        }

        $data = [
            'title'     => 'Edit Data Keuangan',
            'keuangan'  => $keuangan,
            'validation'=> \Config\Services::validation()
        ];

        return view('dashboard/keuangan/edit', $data);
    }

    // 📌 Update data
    public function update($id)
    {
        if (!$this->validate([
            'tanggal'     => 'required',
            'keterangan'  => 'required',
            'pemasukan'   => 'permit_empty|numeric',
            'pengeluaran' => 'permit_empty|numeric'
        ])) {
            return redirect()->back()->withInput();
        }

        $this->keuanganModel->update($id, [
            'tanggal'     => $this->request->getPost('tanggal'),
            'keterangan'  => $this->request->getPost('keterangan'),
            'pemasukan'   => $this->request->getPost('pemasukan') ?: 0,
            'pengeluaran' => $this->request->getPost('pengeluaran') ?: 0,
        ]);

        return redirect()->to('dashboard/keuangan')->with('success', 'Data berhasil diperbarui');
    }

    // 📌 Hapus data
    public function delete($id)
    {
        $this->keuanganModel->delete($id);
        return redirect()->to('dashboard/keuangan')->with('success', 'Data berhasil dihapus');
    }
    // Di controller manapun
}
