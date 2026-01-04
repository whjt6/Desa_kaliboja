<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PosbinduModel;

class PosbinduController extends BaseController
{
    protected $posbinduModel;
    
    public function __construct()
    {
        $this->posbinduModel = new PosbinduModel();
    }
    
    // Index - List Data Posbindu
    public function index()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $dusun = $this->request->getGet('dusun');
        
        $builder = $this->posbinduModel;
        
        if ($dusun) {
            $builder->where('dusun', $dusun);
        }
        
        if ($tahun) {
            $builder->where('tahun', $tahun);
        }
        
        $data['posbindu'] = $builder->orderBy('bulan', 'DESC')->findAll();
        $data['title'] = 'Manajemen Data Posbindu Lansia';
        $data['tahun'] = $tahun;
        $data['dusun'] = $dusun;
        $data['dusunList'] = $this->posbinduModel->getAllDusunNames();
        $data['statistik'] = $this->posbinduModel->getTotalStatistik($tahun);
        $data['statistikDusun'] = $this->posbinduModel->getStatistikByDusun($tahun);
        
        return view('dashboard/posbindu/index', $data);
    }
    
    // Create Form
    public function create()
    {
        $data = [
            'title' => 'Tambah Data Posbindu Lansia',
            'dusunList' => $this->posbinduModel->getAllDusunNames(),
            'validation' => \Config\Services::validation(),
            'bulanSekarang' => date('Y-m')
        ];
        
        return view('dashboard/posbindu/form', $data);
    }
    
    // Store Data
    public function store()
    {
        // Validasi
        $rules = [
            'dusun' => 'required|in_list[semboja_barat,semboja_timur,kaligenteng,silemud]',
            'bulan' => 'required|valid_date[Y-m]',
            'tahun' => 'required|numeric|min_length[4]|max_length[4]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Cek duplikat
        $existing = $this->posbinduModel->where('dusun', $this->request->getPost('dusun'))
                                       ->where('bulan', $this->request->getPost('bulan'))
                                       ->first();
        
        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Data untuk dusun dan bulan tersebut sudah ada!');
        }
        
        // Hitung total lansia
        $totalLansia = ($this->request->getPost('jumlah_lansia_l') ?: 0) + 
                       ($this->request->getPost('jumlah_lansia_p') ?: 0);
        
        // Data untuk disimpan
        $data = [
            'dusun' => $this->request->getPost('dusun'),
            'bulan' => $this->request->getPost('bulan'),
            'tahun' => $this->request->getPost('tahun'),
            
            // Data Lansia
            'jumlah_lansia_l' => $this->request->getPost('jumlah_lansia_l') ?: 0,
            'jumlah_lansia_p' => $this->request->getPost('jumlah_lansia_p') ?: 0,
            'jumlah_lansia_total' => $totalLansia,
            
            // Tekanan Darah
            'tekanan_darah_normal' => $this->request->getPost('tekanan_darah_normal') ?: 0,
            'tekanan_darah_tingkat1' => $this->request->getPost('tekanan_darah_tingkat1') ?: 0,
            'tekanan_darah_tingkat2' => $this->request->getPost('tekanan_darah_tingkat2') ?: 0,
            'tekanan_darah_tingkat3' => $this->request->getPost('tekanan_darah_tingkat3') ?: 0,
            
            // Gula Darah
            'gula_darah_normal' => $this->request->getPost('gula_darah_normal') ?: 0,
            'gula_darah_pradiabetes' => $this->request->getPost('gula_darah_pradiabetes') ?: 0,
            'gula_darah_diabetes' => $this->request->getPost('gula_darah_diabetes') ?: 0,
            
            // IMT
            'imt_kurus' => $this->request->getPost('imt_kurus') ?: 0,
            'imt_normal' => $this->request->getPost('imt_normal') ?: 0,
            'imt_gemuk' => $this->request->getPost('imt_gemuk') ?: 0,
            'imt_obesitas' => $this->request->getPost('imt_obesitas') ?: 0,
            
            // Lingkar Perut
            'lingkar_perut_normal' => $this->request->getPost('lingkar_perut_normal') ?: 0,
            'lingkar_perut_obesitas' => $this->request->getPost('lingkar_perut_obesitas') ?: 0,
            
            // Urin
            'urin_normal' => $this->request->getPost('urin_normal') ?: 0,
            'urin_protein_rendah' => $this->request->getPost('urin_protein_rendah') ?: 0,
            'urin_protein_sedang' => $this->request->getPost('urin_protein_sedang') ?: 0,
            'urin_protein_tinggi' => $this->request->getPost('urin_protein_tinggi') ?: 0,
            
            // Riwayat
            'riwayat_hipertensi' => $this->request->getPost('riwayat_hipertensi') ?: 0,
            'riwayat_diabetes' => $this->request->getPost('riwayat_diabetes') ?: 0,
            'riwayat_jantung' => $this->request->getPost('riwayat_jantung') ?: 0,
            'riwayat_stroke' => $this->request->getPost('riwayat_stroke') ?: 0,
            
            'jumlah_rujukan' => $this->request->getPost('jumlah_rujukan') ?: 0,
            'keterangan' => $this->request->getPost('keterangan')
        ];
        
        if ($this->posbinduModel->save($data)) {
            return redirect()->to('/dashboard/posbindu')->with('success', 'Data posbindu berhasil disimpan!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data posbindu!');
        }
    }
    
    // Edit Form
    public function edit($id)
    {
        $posbindu = $this->posbinduModel->find($id);
        
        if (!$posbindu) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data tidak ditemukan!');
        }
        
        $data = [
            'title' => 'Edit Data Posbindu Lansia',
            'posbindu' => $posbindu,
            'dusunList' => $this->posbinduModel->getAllDusunNames(),
            'validation' => \Config\Services::validation()
        ];
        
        return view('dashboard/posbindu/form', $data);
    }
    
    // Update Data
    public function update($id)
    {
        // Validasi
        $rules = [
            'dusun' => 'required|in_list[semboja_barat,semboja_timur,kaligenteng,silemud]',
            'bulan' => 'required|valid_date[Y-m]',
            'tahun' => 'required|numeric|min_length[4]|max_length[4]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Hitung total lansia
        $totalLansia = ($this->request->getPost('jumlah_lansia_l') ?: 0) + 
                       ($this->request->getPost('jumlah_lansia_p') ?: 0);
        
        // Data untuk update
        $data = [
            'id' => $id,
            'dusun' => $this->request->getPost('dusun'),
            'bulan' => $this->request->getPost('bulan'),
            'tahun' => $this->request->getPost('tahun'),
            
            // Data Lansia
            'jumlah_lansia_l' => $this->request->getPost('jumlah_lansia_l') ?: 0,
            'jumlah_lansia_p' => $this->request->getPost('jumlah_lansia_p') ?: 0,
            'jumlah_lansia_total' => $totalLansia,
            
            // Tekanan Darah
            'tekanan_darah_normal' => $this->request->getPost('tekanan_darah_normal') ?: 0,
            'tekanan_darah_tingkat1' => $this->request->getPost('tekanan_darah_tingkat1') ?: 0,
            'tekanan_darah_tingkat2' => $this->request->getPost('tekanan_darah_tingkat2') ?: 0,
            'tekanan_darah_tingkat3' => $this->request->getPost('tekanan_darah_tingkat3') ?: 0,
            
            // Gula Darah
            'gula_darah_normal' => $this->request->getPost('gula_darah_normal') ?: 0,
            'gula_darah_pradiabetes' => $this->request->getPost('gula_darah_pradiabetes') ?: 0,
            'gula_darah_diabetes' => $this->request->getPost('gula_darah_diabetes') ?: 0,
            
            // IMT
            'imt_kurus' => $this->request->getPost('imt_kurus') ?: 0,
            'imt_normal' => $this->request->getPost('imt_normal') ?: 0,
            'imt_gemuk' => $this->request->getPost('imt_gemuk') ?: 0,
            'imt_obesitas' => $this->request->getPost('imt_obesitas') ?: 0,
            
            // Lingkar Perut
            'lingkar_perut_normal' => $this->request->getPost('lingkar_perut_normal') ?: 0,
            'lingkar_perut_obesitas' => $this->request->getPost('lingkar_perut_obesitas') ?: 0,
            
            // Urin
            'urin_normal' => $this->request->getPost('urin_normal') ?: 0,
            'urin_protein_rendah' => $this->request->getPost('urin_protein_rendah') ?: 0,
            'urin_protein_sedang' => $this->request->getPost('urin_protein_sedang') ?: 0,
            'urin_protein_tinggi' => $this->request->getPost('urin_protein_tinggi') ?: 0,
            
            // Riwayat
            'riwayat_hipertensi' => $this->request->getPost('riwayat_hipertensi') ?: 0,
            'riwayat_diabetes' => $this->request->getPost('riwayat_diabetes') ?: 0,
            'riwayat_jantung' => $this->request->getPost('riwayat_jantung') ?: 0,
            'riwayat_stroke' => $this->request->getPost('riwayat_stroke') ?: 0,
            
            'jumlah_rujukan' => $this->request->getPost('jumlah_rujukan') ?: 0,
            'keterangan' => $this->request->getPost('keterangan')
        ];
        
        if ($this->posbinduModel->save($data)) {
            return redirect()->to('/dashboard/posbindu')->with('success', 'Data posbindu berhasil diperbarui!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data posbindu!');
        }
    }
    
    // Delete Data
    public function delete($id)
    {
        $posbindu = $this->posbinduModel->find($id);
        
        if (!$posbindu) {
            return redirect()->to('/dashboard/posbindu')->with('error', 'Data tidak ditemukan!');
        }
        
        if ($this->posbinduModel->delete($id)) {
            return redirect()->to('/dashboard/posbindu')->with('success', 'Data posbindu berhasil dihapus!');
        } else {
            return redirect()->to('/dashboard/posbindu')->with('error', 'Gagal menghapus data posbindu!');
        }
    }
    // Show Data Detail
public function show($id)
{
    $posbindu = $this->posbinduModel->find($id);
    
    if (!$posbindu) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data tidak ditemukan!');
    }
    
    $data = [
        'title' => 'Detail Data Posbindu Lansia',
        'posbindu' => $posbindu,
        'dusunList' => $this->posbinduModel->getAllDusunNames()
    ];
    
    return view('dashboard/posbindu/show', $data);
}
    
    // Statistik
    public function statistik()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        
        $data = [
            'title' => 'Statistik Posbindu Lansia',
            'tahun' => $tahun,
            'totalStatistik' => $this->posbinduModel->getTotalStatistik($tahun),
            'statistikDusun' => $this->posbinduModel->getStatistikByDusun($tahun),
            'riskDistribution' => $this->posbinduModel->getRiskDistribution($tahun)
        ];
        
        return view('dashboard/posbindu/statistik', $data);
    }
    
    // Export Laporan
    // Export HTML (untuk dicetak sebagai PDF manual)

}