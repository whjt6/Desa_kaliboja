<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PosyanduModel;

class PosyanduController extends BaseController
{
    protected $posyanduModel;
    
    public function __construct()
    {
        $this->posyanduModel = new PosyanduModel();
    }
    
    // Index - List Data Posyandu
    public function index()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $dusun = $this->request->getGet('dusun');
        
        $builder = $this->posyanduModel;
        
        if ($dusun) {
            $builder->where('dusun', $dusun);
        }
        
        if ($tahun) {
            $builder->where('tahun', $tahun);
        }
        
        $data['posyandu'] = $builder->orderBy('bulan', 'DESC')->findAll();
        $data['title'] = 'Manajemen Data Posyandu';
        $data['tahun'] = $tahun;
        $data['dusun'] = $dusun;
        $data['dusunList'] = $this->posyanduModel->getAllDusunNames();
        $data['statistik'] = $this->posyanduModel->getTotalStatistik($tahun);
        $data['statistikDusun'] = $this->posyanduModel->getStatistikByDusun($tahun);
        
        return view('dashboard/posyandu/index', $data);
    }
    
    // Create Form
    public function create()
    {
        $data = [
            'title' => 'Tambah Data Posyandu',
            'dusunList' => $this->posyanduModel->getAllDusunNames(),
            'validation' => \Config\Services::validation(),
            'bulanSekarang' => date('Y-m')
        ];
        
        return view('dashboard/posyandu/form', $data);
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
        $existing = $this->posyanduModel->where('dusun', $this->request->getPost('dusun'))
                                       ->where('bulan', $this->request->getPost('bulan'))
                                       ->first();
        
        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Data untuk dusun dan bulan tersebut sudah ada!');
        }
        
        // Hitung total balita
        $totalBalita = ($this->request->getPost('jumlah_balita_l') ?: 0) + 
                       ($this->request->getPost('jumlah_balita_p') ?: 0);
        
        // Data untuk disimpan
        $data = [
            'dusun' => $this->request->getPost('dusun'),
            'bulan' => $this->request->getPost('bulan'),
            'tahun' => $this->request->getPost('tahun'),
            
            // Balita
            'jumlah_balita_l' => $this->request->getPost('jumlah_balita_l') ?: 0,
            'jumlah_balita_p' => $this->request->getPost('jumlah_balita_p') ?: 0,
            'balita_gizi_buruk' => $this->request->getPost('balita_gizi_buruk') ?: 0,
            'balita_gizi_kurang' => $this->request->getPost('balita_gizi_kurang') ?: 0,
            'balita_gizi_baik' => $this->request->getPost('balita_gizi_baik') ?: 0,
            'balita_gizi_lebih' => $this->request->getPost('balita_gizi_lebih') ?: 0,
            
            // Ibu
            'jumlah_ibu_hamil' => $this->request->getPost('jumlah_ibu_hamil') ?: 0,
            'jumlah_ibu_menyusui' => $this->request->getPost('jumlah_ibu_menyusui') ?: 0,
            
            // Kelahiran
            'kelahiran_l' => $this->request->getPost('kelahiran_l') ?: 0,
            'kelahiran_p' => $this->request->getPost('kelahiran_p') ?: 0,
            'kelahiran_bb_rendah' => $this->request->getPost('kelahiran_bb_rendah') ?: 0,
            
            // Imunisasi
            'imunisasi_dasar_lengkap' => $this->request->getPost('imunisasi_dasar_lengkap') ?: 0,
            'imunisasi_campak' => $this->request->getPost('imunisasi_campak') ?: 0,
            
            'keterangan' => $this->request->getPost('keterangan')
        ];
        
        if ($this->posyanduModel->save($data)) {
            return redirect()->to('/dashboard/posyandu')->with('success', 'Data posyandu berhasil disimpan!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data posyandu!');
        }
    }
    
    // Edit Form
    public function edit($id)
    {
        $posyandu = $this->posyanduModel->find($id);
        
        if (!$posyandu) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data tidak ditemukan!');
        }
        
        $data = [
            'title' => 'Edit Data Posyandu',
            'posyandu' => $posyandu,
            'dusunList' => $this->posyanduModel->getAllDusunNames(),
            'validation' => \Config\Services::validation()
        ];
        
        return view('dashboard/posyandu/form', $data);
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
        
        // Data untuk update
        $data = [
            'id' => $id,
            'dusun' => $this->request->getPost('dusun'),
            'bulan' => $this->request->getPost('bulan'),
            'tahun' => $this->request->getPost('tahun'),
            
            // Balita
            'jumlah_balita_l' => $this->request->getPost('jumlah_balita_l') ?: 0,
            'jumlah_balita_p' => $this->request->getPost('jumlah_balita_p') ?: 0,
            'balita_gizi_buruk' => $this->request->getPost('balita_gizi_buruk') ?: 0,
            'balita_gizi_kurang' => $this->request->getPost('balita_gizi_kurang') ?: 0,
            'balita_gizi_baik' => $this->request->getPost('balita_gizi_baik') ?: 0,
            'balita_gizi_lebih' => $this->request->getPost('balita_gizi_lebih') ?: 0,
            
            // Ibu
            'jumlah_ibu_hamil' => $this->request->getPost('jumlah_ibu_hamil') ?: 0,
            'jumlah_ibu_menyusui' => $this->request->getPost('jumlah_ibu_menyusui') ?: 0,
            
            // Kelahiran
            'kelahiran_l' => $this->request->getPost('kelahiran_l') ?: 0,
            'kelahiran_p' => $this->request->getPost('kelahiran_p') ?: 0,
            'kelahiran_bb_rendah' => $this->request->getPost('kelahiran_bb_rendah') ?: 0,
            
            // Imunisasi
            'imunisasi_dasar_lengkap' => $this->request->getPost('imunisasi_dasar_lengkap') ?: 0,
            'imunisasi_campak' => $this->request->getPost('imunisasi_campak') ?: 0,
            
            'keterangan' => $this->request->getPost('keterangan')
        ];
        
        if ($this->posyanduModel->save($data)) {
            return redirect()->to('/dashboard/posyandu')->with('success', 'Data posyandu berhasil diperbarui!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data posyandu!');
        }
    }
    
    // Delete Data
    public function delete($id)
    {
        $posyandu = $this->posyanduModel->find($id);
        
        if (!$posyandu) {
            return redirect()->to('/dashboard/posyandu')->with('error', 'Data tidak ditemukan!');
        }
        
        if ($this->posyanduModel->delete($id)) {
            return redirect()->to('/dashboard/posyandu')->with('success', 'Data posyandu berhasil dihapus!');
        } else {
            return redirect()->to('/dashboard/posyandu')->with('error', 'Gagal menghapus data posyandu!');
        }
    }
    
    // Show Data Detail
public function show($id)
{
    $posyandu = $this->posyanduModel->find($id);
    
    if (!$posyandu) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data tidak ditemukan!');
    }
    
    $data = [
        'title' => 'Detail Data Posyandu',
        'posyandu' => $posyandu,
        'dusunList' => $this->posyanduModel->getAllDusunNames()
    ];
    
    return view('dashboard/posyandu/show', $data);
}
    
    // Statistik dan Laporan
    public function statistik()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        
        $data = [
            'title' => 'Statistik Posyandu',
            'tahun' => $tahun,
            'totalStatistik' => $this->posyanduModel->getTotalStatistik($tahun),
            'statistikDusun' => $this->posyanduModel->getStatistikByDusun($tahun),
            'monthlyData' => $this->posyanduModel->getMonthlyData(null, $tahun)
        ];
        
        return view('dashboard/posyandu/statistik', $data);
    }
    
    // Print Report (HTML untuk dicetak sebagai PDF)

}