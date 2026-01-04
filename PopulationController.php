<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PopulationModel;

class PopulationController extends BaseController
{
    protected $populationModel;

    public function __construct()
    {
        $this->populationModel = new PopulationModel();
    }

    public function index()
    {
        $genderData = $this->populationModel->getGenderStats();
        $laki_laki = 0;
        $perempuan = 0;

        foreach ($genderData as $row) {
            if ($row['gender'] === 'L') {
                $laki_laki = $row['total'];
            } elseif ($row['gender'] === 'P') {
                $perempuan = $row['total'];
            }
        }

        $data = [
            'title'       => 'Manajemen Data Penduduk',
            'populations' => $this->populationModel->orderBy('NO', 'DESC')->findAll(),
            'stats'       => [
                'total'     => $laki_laki + $perempuan,
                'laki_laki' => $laki_laki,
                'perempuan' => $perempuan,
            ]
        ];

        return view('dashboard/population/index', $data);
    }

    public function create()
    {
        return view('dashboard/population/form', [
            'title'      => 'Tambah Data Penduduk',
            'population' => [],
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        // Validasi input
        $rules = [
            'NO'         => 'required|numeric',
            'RW'         => 'required',
            'RT'         => 'required',
            'NO_RUMAH'   => 'required',
            'NO_KK'      => 'required',
            'NIK'        => 'required|min_length[16]|max_length[16]|is_unique[populations.NIK]',
            'NAMA'       => 'required',
            'JK'         => 'required|in_list[L,P]',
            'TMPT_LHR'   => 'required',
            'TGL_LHR'    => 'required|valid_date',
            'AGAMA'      => 'required',
            'STATUS'     => 'required',
            'SHDK'       => 'required',
            'PDDK_AKHIR' => 'required',
            'PEKERJAAN'  => 'required',
            'NAMA_AYAH'  => 'required',
            'NAMA_IBU'   => 'required',
            'ALAMAT'     => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $umur = $this->getUmur($this->request->getPost('TGL_LHR'));

        $data = [
            'NO'         => $this->request->getPost('NO'),
            'RW'         => $this->request->getPost('RW'),
            'RT'         => $this->request->getPost('RT'),
            'NO_RUMAH'   => $this->request->getPost('NO_RUMAH'),
            'NO_KK'      => $this->request->getPost('NO_KK'),
            'NIK'        => $this->request->getPost('NIK'),
            'NAMA'       => $this->request->getPost('NAMA'),
            'JK'         => strtoupper($this->request->getPost('JK')),
            'TMPT_LHR'   => $this->request->getPost('TMPT_LHR'),
            'TGL_LHR'    => $this->request->getPost('TGL_LHR'),
            'AGAMA'      => $this->request->getPost('AGAMA'),
            'STATUS'     => $this->request->getPost('STATUS'),
            'SHDK'       => $this->request->getPost('SHDK'),
            'PDDK_AKHIR' => $this->request->getPost('PDDK_AKHIR'),
            'PEKERJAAN'  => $this->request->getPost('PEKERJAAN'),
            'NAMA_AYAH'  => $this->request->getPost('NAMA_AYAH'),
            'NAMA_IBU'   => $this->request->getPost('NAMA_IBU'),
            'T'          => $umur['tahun'],
            'B'          => $umur['bulan'],
            'ALAMAT'     => $this->request->getPost('ALAMAT'),
        ];

        if ($this->populationModel->save($data)) {
            session()->setFlashdata('success', 'Data penduduk berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan data penduduk.');
        }
        
        return redirect()->to('/dashboard/population');
    }

    public function edit($id)
    {
        $population = $this->populationModel->find($id);
        if (!$population) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Data dengan ID $id tidak ditemukan.");
        }

        return view('dashboard/population/form', [
            'title'      => 'Edit Data Penduduk',
            'population' => $population,
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update($id)
    {
        // Validasi input
        $rules = [
            'NO'         => 'required|numeric',
            'RW'         => 'required',
            'RT'         => 'required',
            'NO_RUMAH'   => 'required',
            'NO_KK'      => 'required',
            'NIK'        => "required|min_length[16]|max_length[16]|is_unique[populations.NIK,id,{$id}]",
            'NAMA'       => 'required',
            'JK'         => 'required|in_list[L,P]',
            'TMPT_LHR'   => 'required',
            'TGL_LHR'    => 'required|valid_date',
            'AGAMA'      => 'required',
            'STATUS'     => 'required',
            'SHDK'       => 'required',
            'PDDK_AKHIR' => 'required',
            'PEKERJAAN'  => 'required',
            'NAMA_AYAH'  => 'required',
            'NAMA_IBU'   => 'required',
            'ALAMAT'     => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $umur = $this->getUmur($this->request->getPost('TGL_LHR'));

        $data = [
            'NO'         => $this->request->getPost('NO'),
            'RW'         => $this->request->getPost('RW'),
            'RT'         => $this->request->getPost('RT'),
            'NO_RUMAH'   => $this->request->getPost('NO_RUMAH'),
            'NO_KK'      => $this->request->getPost('NO_KK'),
            'NIK'        => $this->request->getPost('NIK'),
            'NAMA'       => $this->request->getPost('NAMA'),
            'JK'         => $this->request->getPost('JK'),
            'TMPT_LHR'   => $this->request->getPost('TMPT_LHR'),
            'TGL_LHR'    => $this->request->getPost('TGL_LHR'),
            'AGAMA'      => $this->request->getPost('AGAMA'),
            'STATUS'     => $this->request->getPost('STATUS'),
            'SHDK'       => $this->request->getPost('SHDK'),
            'PDDK_AKHIR' => $this->request->getPost('PDDK_AKHIR'),
            'PEKERJAAN'  => $this->request->getPost('PEKERJAAN'),
            'NAMA_AYAH'  => $this->request->getPost('NAMA_AYAH'),
            'NAMA_IBU'   => $this->request->getPost('NAMA_IBU'),
            'T'          => $umur['tahun'],
            'B'          => $umur['bulan'],
            'ALAMAT'     => $this->request->getPost('ALAMAT'),
        ];

        if ($this->populationModel->update($id, $data)) {
            session()->setFlashdata('success', 'Data penduduk berhasil diperbarui.');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui data penduduk.');
        }
        
        return redirect()->to('/dashboard/population');
    }

    public function delete($id)
    {
        $population = $this->populationModel->find($id);
        if ($population) {
            if ($this->populationModel->delete($id)) {
                session()->setFlashdata('success', 'Data berhasil dihapus.');
            } else {
                session()->setFlashdata('error', 'Gagal menghapus data.');
            }
        } else {
            session()->setFlashdata('error', 'Data tidak ditemukan.');
        }
        return redirect()->to('/dashboard/population');
    }

    private function getUmur($tanggalLahir)
    {
        $tglLahir = new \DateTime($tanggalLahir);
        $sekarang = new \DateTime();
        $diff = $sekarang->diff($tglLahir);

        return [
            'tahun' => $diff->y,
            'bulan' => $diff->m
        ];
    }
    // Di controller manapun

}