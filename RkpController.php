<?php
namespace App\Controllers;

use App\Models\RkpModel;

class RkpController extends BaseController
{
    protected $rkpModel;

    public function __construct()
    {
        $this->rkpModel = new RkpModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen RKP Desa',
            'rkps' => $this->rkpModel->orderBy('tahun', 'DESC')->orderBy('created_at', 'DESC')->findAll(),
            'tahunList' => $this->rkpModel->getTahunList()
        ];
        
        return view('dashboard/rkp/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Rencana Kegiatan',
            'validation' => \Config\Services::validation()
        ];
        
        return view('dashboard/rkp/create', $data);
    }

    public function store()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'tahun' => 'required|numeric',
            'nama_kegiatan' => 'required|min_length[5]',
            'lokasi' => 'required',
            'volume' => 'required',
            'sasaran' => 'required',
            'waktu_pelaksanaan' => 'required',
            'jumlah_biaya' => 'required|numeric',
            'sumber_dana' => 'required',
            'pelaksana' => 'required',
            'status' => 'required',
            'progress' => 'required|numeric'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'tahun' => $this->request->getPost('tahun'),
            'nama_kegiatan' => $this->request->getPost('nama_kegiatan'),
            'lokasi' => $this->request->getPost('lokasi'),
            'volume' => $this->request->getPost('volume'),
            'sasaran' => $this->request->getPost('sasaran'),
            'waktu_pelaksanaan' => $this->request->getPost('waktu_pelaksanaan'),
            'jumlah_biaya' => $this->request->getPost('jumlah_biaya'),
            'sumber_dana' => $this->request->getPost('sumber_dana'),
            'pelaksana' => $this->request->getPost('pelaksana'),
            'keterangan' => $this->request->getPost('keterangan'),
            'status' => $this->request->getPost('status'),
            'progress' => $this->request->getPost('progress')
        ];

        if ($this->rkpModel->save($data)) {
            return redirect()->to('/dashboard/rkp')->with('success', 'Rencana kegiatan berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan rencana kegiatan');
        }
    }

    public function edit($id)
    {
        $rkp = $this->rkpModel->find($id);
        if (!$rkp) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Rencana Kegiatan',
            'rkp' => $rkp,
            'validation' => \Config\Services::validation()
        ];
        
        return view('dashboard/rkp/edit', $data);
    }

    public function update($id)
    {
        $rkp = $this->rkpModel->find($id);
        if (!$rkp) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'tahun' => 'required|numeric',
            'nama_kegiatan' => 'required|min_length[5]',
            'lokasi' => 'required',
            'volume' => 'required',
            'sasaran' => 'required',
            'waktu_pelaksanaan' => 'required',
            'jumlah_biaya' => 'required|numeric',
            'sumber_dana' => 'required',
            'pelaksana' => 'required',
            'status' => 'required',
            'progress' => 'required|numeric'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'tahun' => $this->request->getPost('tahun'),
            'nama_kegiatan' => $this->request->getPost('nama_kegiatan'),
            'lokasi' => $this->request->getPost('lokasi'),
            'volume' => $this->request->getPost('volume'),
            'sasaran' => $this->request->getPost('sasaran'),
            'waktu_pelaksanaan' => $this->request->getPost('waktu_pelaksanaan'),
            'jumlah_biaya' => $this->request->getPost('jumlah_biaya'),
            'sumber_dana' => $this->request->getPost('sumber_dana'),
            'pelaksana' => $this->request->getPost('pelaksana'),
            'keterangan' => $this->request->getPost('keterangan'),
            'status' => $this->request->getPost('status'),
            'progress' => $this->request->getPost('progress')
        ];

        if ($this->rkpModel->update($id, $data)) {
            return redirect()->to('/dashboard/rkp')->with('success', 'Rencana kegiatan berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui rencana kegiatan');
        }
    }

    public function delete($id)
    {
        $rkp = $this->rkpModel->find($id);
        if (!$rkp) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
        
        if ($this->rkpModel->delete($id)) {
            return redirect()->to('/dashboard/rkp')->with('success', 'Rencana kegiatan berhasil dihapus');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus rencana kegiatan');
        }
    }

    public function laporan()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        
        $data = [
            'title' => 'Laporan RKP Desa Tahun ' . $tahun,
            'rkps' => $this->rkpModel->where('tahun', $tahun)->findAll(),
            'tahun' => $tahun,
            'tahunList' => $this->rkpModel->getTahunList()
        ];
        
        return view('dashboard/rkp/laporan', $data);
    }

    public function statistik()
    {
        $db = \Config\Database::connect();
        $statistik = $db->query("
            SELECT tahun, COUNT(*) as total_kegiatan, SUM(jumlah_biaya) as total_biaya 
            FROM rkp_desa 
            GROUP BY tahun 
            ORDER BY tahun DESC
        ")->getResultArray();

        $data = [
            'title' => 'Statistik RKP Desa',
            'statistik' => $statistik
        ];
        
        return view('dashboard/rkp/statistik', $data);
    }
}