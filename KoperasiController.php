<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KoperasiModel;
use App\Models\KoperasiUnitUsahaModel;
use App\Models\KoperasiBeritaModel;
use App\Models\KoperasiLaporanModel;
use App\Models\KoperasiPendaftaranModel;

class KoperasiController extends BaseController
{
    protected $koperasiModel;
    protected $unitUsahaModel;
    protected $beritaModel;
    protected $laporanModel;
    protected $pendaftaranModel;

    public function __construct()
    {
        $this->koperasiModel = new KoperasiModel();
        $this->unitUsahaModel = new KoperasiUnitUsahaModel();
        $this->beritaModel = new KoperasiBeritaModel();
        $this->laporanModel = new KoperasiLaporanModel();
        $this->pendaftaranModel = new KoperasiPendaftaranModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Koperasi Merah Putih Desa Kaliboja',
            'profile' => $this->koperasiModel->getProfile(),
            'statistik' => $this->koperasiModel->getStatistik(),
            'berita_terbaru' => $this->beritaModel->getLatest(3),
            'unit_populer' => $this->unitUsahaModel->getPopular(6),
            'laporan_terbaru' => $this->laporanModel->getLatest(3)
        ];

        return view('koperasi/index', $data);
    }

    public function profil()
    {
        $data = [
            'title' => 'Profil Koperasi Merah Putih',
            'profile' => $this->koperasiModel->getProfile(),
            'visi_misi' => $this->koperasiModel->getVisiMisi(),
            'struktur' => $this->koperasiModel->getStruktur()
        ];

        return view('koperasi/profil', $data);
    }

    public function unitUsaha()
    {
        $kategori = $this->request->getGet('kategori');
        $search = $this->request->getGet('search');
        
        $data = [
            'title' => 'Unit Usaha Koperasi',
            'units' => $this->unitUsahaModel->getAllWithKategori($kategori, $search),
            'kategories' => $this->unitUsahaModel->getKategoriList(),
            'currentKategori' => $kategori,
            'searchTerm' => $search
        ];

        return view('koperasi/unit_usaha', $data);
    }

    public function detailUnit($id)
    {
        $unit = $this->unitUsahaModel->findWithKategori($id);
        
        if (!$unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => $unit['nama_unit'],
            'unit' => $unit,
            'relatedUnits' => $this->unitUsahaModel->getRelated($unit['kategori'], $id, 4)
        ];

        return view('koperasi/detail_unit', $data);
    }

    public function keanggotaan()
    {
        $data = [
            'title' => 'Keanggotaan Koperasi',
            'persyaratan' => $this->koperasiModel->getPersyaratan(),
            'manfaat' => $this->koperasiModel->getManfaatAnggota(),
            'simpanan' => $this->koperasiModel->getInfoSimpanan()
        ];

        return view('koperasi/keanggotaan', $data);
    }

    public function formPendaftaran()
    {
        if ($this->request->getMethod() === 'post') {
            return $this->submitPendaftaran();
        }

        $data = [
            'title' => 'Formulir Pendaftaran Anggota',
            'validation' => \Config\Services::validation()
        ];

        return view('koperasi/form_pendaftaran', $data);
    }

    public function submitPendaftaran()
    {
        $rules = [
            'nama' => 'required|min_length[3]|max_length[100]',
            'nik' => 'required|min_length[16]|max_length[16]',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|valid_date',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'alamat' => 'required',
            'no_hp' => 'required|min_length[10]|max_length[15]',
            'email' => 'valid_email',
            'pekerjaan' => 'required',
            'simpanan_pokok' => 'required|numeric',
            'simpanan_wajib' => 'required|numeric',
            'foto_ktp' => 'uploaded[foto_ktp]|max_size[foto_ktp,2048]|is_image[foto_ktp]|mime_in[foto_ktp,image/jpg,image/jpeg,image/png]',
            'foto_diri' => 'uploaded[foto_diri]|max_size[foto_diri,2048]|is_image[foto_diri]|mime_in[foto_diri,image/jpg,image/jpeg,image/png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Upload foto
        $fotoKTP = $this->request->getFile('foto_ktp');
        $fotoDiri = $this->request->getFile('foto_diri');
        
        if ($fotoKTP->isValid() && !$fotoKTP->hasMoved()) {
            $newNameKTP = $fotoKTP->getRandomName();
            $fotoKTP->move(ROOTPATH . 'public/uploads/koperasi/ktp/', $newNameKTP);
        }

        if ($fotoDiri->isValid() && !$fotoDiri->hasMoved()) {
            $newNameDiri = $fotoDiri->getRandomName();
            $fotoDiri->move(ROOTPATH . 'public/uploads/koperasi/foto/', $newNameDiri);
        }

        $data = [
            'kode_pendaftaran' => 'KP-' . date('Ymd') . '-' . substr(uniqid(), -6),
            'nama' => $this->request->getPost('nama'),
            'nik' => $this->request->getPost('nik'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'alamat' => $this->request->getPost('alamat'),
            'no_hp' => $this->request->getPost('no_hp'),
            'email' => $this->request->getPost('email'),
            'pekerjaan' => $this->request->getPost('pekerjaan'),
            'simpanan_pokok' => $this->request->getPost('simpanan_pokok'),
            'simpanan_wajib' => $this->request->getPost('simpanan_wajib'),
            'foto_ktp' => $newNameKTP,
            'foto_diri' => $newNameDiri,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->pendaftaranModel->save($data);

        // Kirim notifikasi WhatsApp (jika diaktifkan)
        $this->sendWhatsAppNotification($data);

        session()->setFlashdata('success', 'Pendaftaran berhasil dikirim. Admin akan menghubungi Anda dalam 1x24 jam.');
        return redirect()->to('/koperasi/keanggotaan');
    }

    public function laporan()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $jenis = $this->request->getGet('jenis');
        
        $data = [
            'title' => 'Laporan Koperasi',
            'laporan' => $this->laporanModel->getByTahun($tahun, $jenis),
            'tahun_list' => $this->laporanModel->getTahunList(),
            'currentTahun' => $tahun,
            'currentJenis' => $jenis
        ];

        return view('koperasi/laporan', $data);
    }

    public function berita()
    {
        $search = $this->request->getGet('search');
        
        $data = [
            'title' => 'Berita & Kegiatan Koperasi',
            'berita' => $this->beritaModel->getAll($search),
            'searchTerm' => $search
        ];

        return view('koperasi/berita', $data);
    }

    public function kontak()
    {
        $data = [
            'title' => 'Kontak Koperasi',
            'kontak' => $this->koperasiModel->getKontak(),
            'jam_operasional' => $this->koperasiModel->getJamOperasional()
        ];

        return view('koperasi/kontak', $data);
    }

    private function sendWhatsAppNotification($data)
    {
        $adminPhone = $this->koperasiModel->getKontak()['whatsapp'] ?? '';
        
        if (!empty($adminPhone)) {
            $message = "📋 *PENDAFTARAN ANGGOTA BARU*\n\n";
            $message .= "Nama: *{$data['nama']}*\n";
            $message .= "NIK: {$data['nik']}\n";
            $message .= "No. HP: {$data['no_hp']}\n";
            $message .= "Alamat: {$data['alamat']}\n";
            $message .= "Simpanan Pokok: Rp " . number_format($data['simpanan_pokok'], 0, ',', '.') . "\n";
            $message .= "Simpanan Wajib: Rp " . number_format($data['simpanan_wajib'], 0, ',', '.') . "\n\n";
            $message .= "Kode Pendaftaran: *{$data['kode_pendaftaran']}*\n";
            $message .= "Waktu: " . date('d/m/Y H:i:s');
            
            // Kirim via WhatsApp API
            $apiUrl = "https://api.whatsapp.com/send?phone=$adminPhone&text=" . urlencode($message);
            
            // Anda bisa mengirimkan notifikasi ke sistem Anda sendiri
            // atau menggunakan service seperti WhatsApp Business API
        }
    }
    

    public function detailPendaftaran($id)
    {
        // Hanya untuk admin
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $pendaftaran = $this->pendaftaranModel->find($id);
        
        if (!$pendaftaran) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Detail Pendaftaran',
            'pendaftaran' => $pendaftaran
        ];

        return view('dashboard/koperasi/pendaftaran/detail', $data);
    }

    public function updateStatusPendaftaran($id)
    {
        // Hanya untuk admin
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $status = $this->request->getPost('status');
        
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->pendaftaranModel->update($id, $data)) {
            session()->setFlashdata('success', 'Status pendaftaran berhasil diperbarui.');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui status.');
        }

        return redirect()->back();
    }
    public function detailBerita($slug = null)
{
    if (!$slug) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $berita = $this->beritaModel->where('slug', $slug)
                                 ->where('status', 'published')
                                 ->first();
    
    if (!$berita) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Increment view count jika ada method
    if (method_exists($this->beritaModel, 'incrementView')) {
        $this->beritaModel->incrementView($berita['id']);
    }

    // Ambil berita terkait
    $berita_terkait = $this->beritaModel->where('slug !=', $slug)
                                        ->where('status', 'published')
                                        ->orderBy('created_at', 'DESC')
                                        ->limit(3)
                                        ->findAll();

    $data = [
        'title' => $berita['judul'],
        'berita' => $berita,
        'berita_terkait' => $berita_terkait,
        'profile' => $this->koperasiModel->getProfile()
    ];

    return view('koperasi/detail_berita', $data);
}

public function downloadLaporan($id)
{
    $laporan = $this->laporanModel->find($id);
    
    if (!$laporan || !$laporan['file_path']) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $filePath = ROOTPATH . 'public/uploads/koperasi/laporan/' . $laporan['file_path'];
    
    if (!file_exists($filePath)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    return $this->response->download($filePath, null);
}
}