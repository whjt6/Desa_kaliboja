<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KoperasiModel;
use App\Models\KoperasiAnggotaModel;
use App\Models\KoperasiUnitUsahaModel;
use App\Models\KoperasiSimpananModel;
use App\Models\KoperasiLaporanModel;
use App\Models\KoperasiBeritaModel;
use App\Models\KoperasiPendaftaranModel;
use App\Models\KoperasiSliderModel;

class KoperasiAdminController extends BaseController
{
    protected $koperasiModel;
    protected $anggotaModel;
    protected $unitUsahaModel;
    protected $simpananModel;
    protected $laporanModel;
    protected $beritaModel;
    protected $pendaftaranModel;
    protected $sliderModel;

    public function __construct()
    {
        $this->koperasiModel = new KoperasiModel();
        $this->anggotaModel = new KoperasiAnggotaModel();
        $this->unitUsahaModel = new KoperasiUnitUsahaModel();
        $this->simpananModel = new KoperasiSimpananModel();
        $this->laporanModel = new KoperasiLaporanModel();
        $this->beritaModel = new KoperasiBeritaModel();
        $this->pendaftaranModel = new KoperasiPendaftaranModel();
        $this->sliderModel = new KoperasiSliderModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Dashboard Koperasi',
            'total_anggota' => $this->anggotaModel->countAll(),
            'total_simpanan' => $this->simpananModel->getTotalSaldo(),
            'total_unit' => $this->unitUsahaModel->countAll(),
            'pendaftaran_pending' => $this->pendaftaranModel->where('status', 'pending')->countAllResults(),
            'pendaftaran_baru' => $this->pendaftaranModel->orderBy('created_at', 'DESC')->findAll(5),
            'grafik_anggota' => $this->anggotaModel->getGrafikPendaftaran(),
            'grafik_simpanan' => $this->simpananModel->getGrafikSimpanan()
        ];

        return view('dashboard/koperasi/index', $data);
    }

    // MANAJEMEN ANGGOTA
    public function anggota()
    {
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');
        
        $data = [
            'title' => 'Manajemen Anggota Koperasi',
            'anggota' => $this->anggotaModel->getAllWithFilter($search, $status),
            'searchTerm' => $search,
            'currentStatus' => $status
        ];

        return view('dashboard/koperasi/anggota/index', $data);
    }

    public function createAnggota()
    {
        $data = [
            'title' => 'Tambah Anggota Baru',
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/anggota/form', $data);
    }

    public function storeAnggota()
    {
        $rules = [
            'nama' => 'required|min_length[3]|max_length[100]',
            'nik' => 'required|min_length[16]|max_length[16]|is_unique[koperasi_anggota.nik]',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|valid_date',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'alamat' => 'required',
            'no_hp' => 'required|min_length[10]|max_length[15]',
            'email' => 'valid_email',
            'pekerjaan' => 'required',
            'tanggal_daftar' => 'required|valid_date',
            'simpanan_pokok' => 'required|numeric',
            'simpanan_wajib' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_anggota' => 'KA-' . date('Ym') . '-' . str_pad($this->anggotaModel->countAll() + 1, 4, '0', STR_PAD_LEFT),
            'nama' => $this->request->getPost('nama'),
            'nik' => $this->request->getPost('nik'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'alamat' => $this->request->getPost('alamat'),
            'no_hp' => $this->request->getPost('no_hp'),
            'email' => $this->request->getPost('email'),
            'pekerjaan' => $this->request->getPost('pekerjaan'),
            'tanggal_daftar' => $this->request->getPost('tanggal_daftar'),
            'simpanan_pokok' => $this->request->getPost('simpanan_pokok'),
            'simpanan_wajib' => $this->request->getPost('simpanan_wajib'),
            'status' => 'aktif',
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->anggotaModel->save($data)) {
            // Simpan transaksi simpanan pertama
            $simpananData = [
                'anggota_id' => $this->anggotaModel->getInsertID(),
                'jenis' => 'pokok',
                'jumlah' => $data['simpanan_pokok'],
                'tanggal' => $data['tanggal_daftar'],
                'keterangan' => 'Simpanan Pokok Awal',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->simpananModel->save($simpananData);

            session()->setFlashdata('success', 'Anggota berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan anggota.');
        }

        return redirect()->to('/dashboard/koperasi/anggota');
    }

    public function editAnggota($id)
    {
        $anggota = $this->anggotaModel->find($id);
        
        if (!$anggota) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Data Anggota',
            'anggota' => $anggota,
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/anggota/form', $data);
    }

    public function updateAnggota($id)
    {
        $rules = [
            'nama' => 'required|min_length[3]|max_length[100]',
            'nik' => "required|min_length[16]|max_length[16]|is_unique[koperasi_anggota.nik,id,{$id}]",
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|valid_date',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'alamat' => 'required',
            'no_hp' => 'required|min_length[10]|max_length[15]',
            'email' => 'valid_email',
            'pekerjaan' => 'required',
            'tanggal_daftar' => 'required|valid_date',
            'status' => 'required|in_list[aktif,nonaktif,keluar]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'nik' => $this->request->getPost('nik'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'alamat' => $this->request->getPost('alamat'),
            'no_hp' => $this->request->getPost('no_hp'),
            'email' => $this->request->getPost('email'),
            'pekerjaan' => $this->request->getPost('pekerjaan'),
            'tanggal_daftar' => $this->request->getPost('tanggal_daftar'),
            'status' => $this->request->getPost('status'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->anggotaModel->update($id, $data)) {
            session()->setFlashdata('success', 'Data anggota berhasil diperbarui.');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui data anggota.');
        }

        return redirect()->to('/dashboard/koperasi/anggota');
    }

    public function deleteAnggota($id)
    {
        if ($this->anggotaModel->delete($id)) {
            session()->setFlashdata('success', 'Anggota berhasil dihapus.');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus anggota.');
        }

        return redirect()->to('/dashboard/koperasi/anggota');
    }

    public function updateStatus($id)
    {
        $status = $this->request->getPost('status');
        
        if ($this->anggotaModel->update($id, ['status' => $status])) {
            session()->setFlashdata('success', 'Status anggota berhasil diperbarui.');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui status.');
        }

        return redirect()->to('/dashboard/koperasi/anggota');
    }

    public function exportAnggota()
    {
        $anggota = $this->anggotaModel->findAll();
        
        $filename = "data-anggota-koperasi-" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, [
            'Kode Anggota', 'Nama', 'NIK', 'Tempat Lahir', 'Tanggal Lahir', 
            'Jenis Kelamin', 'Alamat', 'No HP', 'Email', 'Pekerjaan',
            'Tanggal Daftar', 'Simpanan Pokok', 'Simpanan Wajib', 'Status'
        ]);
        
        // Data
        foreach ($anggota as $row) {
            fputcsv($output, [
                $row['kode_anggota'],
                $row['nama'],
                $row['nik'],
                $row['tempat_lahir'],
                $row['tanggal_lahir'],
                $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan',
                $row['alamat'],
                $row['no_hp'],
                $row['email'],
                $row['pekerjaan'],
                $row['tanggal_daftar'],
                $row['simpanan_pokok'],
                $row['simpanan_wajib'],
                $row['status']
            ]);
        }
        
        fclose($output);
        exit();
    }

    // MANAJEMEN UNIT USAHA
    public function unitUsaha()
    {
        $data = [
            'title' => 'Manajemen Unit Usaha',
            'units' => $this->unitUsahaModel->getAllWithKategori(),
            'kategories' => $this->unitUsahaModel->getKategoriList()
        ];

        return view('dashboard/koperasi/unit_usaha/index', $data);
    }

    public function createUnit()
    {
        $data = [
            'title' => 'Tambah Unit Usaha',
            'kategories' => $this->unitUsahaModel->getKategoriList(),
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/unit_usaha/form', $data);
    }

    public function storeUnit()
    {
        $rules = [
            'nama_unit' => 'required|min_length[3]|max_length[100]',
            'kategori' => 'required',
            'deskripsi' => 'required',
            'harga' => 'required|numeric',
            'satuan' => 'required',
            'stok' => 'required|numeric',
            'status' => 'required|in_list[tersedia,habis,preorder]',
            'gambar' => 'uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('gambar');
        
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/koperasi/unit/', $newName);
        }

        $data = [
            'kode_unit' => 'KU-' . date('Ym') . '-' . str_pad($this->unitUsahaModel->countAll() + 1, 3, '0', STR_PAD_LEFT),
            'nama_unit' => $this->request->getPost('nama_unit'),
            'kategori' => $this->request->getPost('kategori'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'harga' => $this->request->getPost('harga'),
            'satuan' => $this->request->getPost('satuan'),
            'stok' => $this->request->getPost('stok'),
            'status' => $this->request->getPost('status'),
            'gambar' => $newName,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->unitUsahaModel->save($data)) {
            session()->setFlashdata('success', 'Unit usaha berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan unit usaha.');
        }

        return redirect()->to('/dashboard/koperasi/unit-usaha');
    }

    public function editUnit($id)
    {
        $unit = $this->unitUsahaModel->find($id);
        
        if (!$unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Unit Usaha',
            'unit' => $unit,
            'kategories' => $this->unitUsahaModel->getKategoriList(),
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/unit_usaha/form', $data);
    }

    public function updateUnit($id)
    {
        $rules = [
            'nama_unit' => 'required|min_length[3]|max_length[100]',
            'kategori' => 'required',
            'deskripsi' => 'required',
            'harga' => 'required|numeric',
            'satuan' => 'required',
            'stok' => 'required|numeric',
            'status' => 'required|in_list[tersedia,habis,preorder]',
            'gambar' => 'if_exist|uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama_unit' => $this->request->getPost('nama_unit'),
            'kategori' => $this->request->getPost('kategori'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'harga' => $this->request->getPost('harga'),
            'satuan' => $this->request->getPost('satuan'),
            'stok' => $this->request->getPost('stok'),
            'status' => $this->request->getPost('status'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Upload gambar baru jika ada
        $file = $this->request->getFile('gambar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/koperasi/unit/', $newName);
            
            // Hapus gambar lama
            $oldUnit = $this->unitUsahaModel->find($id);
            if ($oldUnit['gambar'] && file_exists(ROOTPATH . 'public/uploads/koperasi/unit/' . $oldUnit['gambar'])) {
                unlink(ROOTPATH . 'public/uploads/koperasi/unit/' . $oldUnit['gambar']);
            }
            
            $data['gambar'] = $newName;
        }

        if ($this->unitUsahaModel->update($id, $data)) {
            session()->setFlashdata('success', 'Unit usaha berhasil diperbarui.');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui unit usaha.');
        }

        return redirect()->to('/dashboard/koperasi/unit-usaha');
    }

    public function deleteUnit($id)
    {
        $unit = $this->unitUsahaModel->find($id);
        
        if ($unit) {
            // Hapus gambar
            if ($unit['gambar'] && file_exists(ROOTPATH . 'public/uploads/koperasi/unit/' . $unit['gambar'])) {
                unlink(ROOTPATH . 'public/uploads/koperasi/unit/' . $unit['gambar']);
            }
            
            if ($this->unitUsahaModel->delete($id)) {
                session()->setFlashdata('success', 'Unit usaha berhasil dihapus.');
            } else {
                session()->setFlashdata('error', 'Gagal menghapus unit usaha.');
            }
        }

        return redirect()->to('/dashboard/koperasi/unit-usaha');
    }

    public function kategoriUnit()
    {
        $data = [
            'title' => 'Kategori Unit Usaha',
            'kategories' => $this->unitUsahaModel->getKategoriList()
        ];

        return view('dashboard/koperasi/unit_usaha/kategori', $data);
    }

    public function storeKategori()
    {
        $kategori = $this->request->getPost('nama_kategori');
        
        if (empty($kategori)) {
            session()->setFlashdata('error', 'Nama kategori tidak boleh kosong.');
            return redirect()->back();
        }

        // Simpan ke database atau konfigurasi
        $existing = $this->unitUsahaModel->getKategoriList();
        if (!in_array($kategori, $existing)) {
            $existing[] = $kategori;
            // Simpan ke database atau file konfigurasi
            $this->unitUsahaModel->saveKategori($existing);
            session()->setFlashdata('success', 'Kategori berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Kategori sudah ada.');
        }

        return redirect()->to('/dashboard/koperasi/unit-usaha/kategori');
    }

    public function deleteKategori($index)
    {
        $existing = $this->unitUsahaModel->getKategoriList();
        
        if (isset($existing[$index])) {
            unset($existing[$index]);
            $existing = array_values($existing); // Reset index
            
            // Simpan kembali
            $this->unitUsahaModel->saveKategori($existing);
            session()->setFlashdata('success', 'Kategori berhasil dihapus.');
        }

        return redirect()->to('/dashboard/koperasi/unit-usaha/kategori');
    }

    // MANAJEMEN SIMPANAN
    public function simpanan()
    {
        $anggota_id = $this->request->getGet('anggota_id');
        $jenis = $this->request->getGet('jenis');
        $bulan = $this->request->getGet('bulan');
        
        $data = [
            'title' => 'Manajemen Simpanan',
            'simpanan' => $this->simpananModel->getAllWithFilter($anggota_id, $jenis, $bulan),
            'anggota_list' => $this->anggotaModel->findAll(),
            'currentAnggota' => $anggota_id,
            'currentJenis' => $jenis,
            'currentBulan' => $bulan
        ];

        return view('dashboard/koperasi/simpanan/index', $data);
    }

    public function createSimpanan()
    {
        $data = [
            'title' => 'Tambah Transaksi Simpanan',
            'anggota_list' => $this->anggotaModel->where('status', 'aktif')->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/simpanan/form', $data);
    }

    public function storeSimpanan()
    {
        $rules = [
            'anggota_id' => 'required|numeric',
            'jenis' => 'required|in_list[pokok,wajib,sukarela]',
            'jumlah' => 'required|numeric|greater_than[0]',
            'tanggal' => 'required|valid_date',
            'keterangan' => 'required|min_length[3]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

$data = [
        'kode_transaksi' => $this->simpananModel->generateKodeTransaksi(),  // TAMBAHAN
        'anggota_id' => $this->request->getPost('anggota_id'),
        'jenis' => $this->request->getPost('jenis'),
        'jumlah' => $this->request->getPost('jumlah'),
        'tanggal' => $this->request->getPost('tanggal'),
        'keterangan' => $this->request->getPost('keterangan'),
        'created_at' => date('Y-m-d H:i:s')
    ];
        if ($this->simpananModel->save($data)) {
            // Update total simpanan di tabel anggota
            $anggota = $this->anggotaModel->find($data['anggota_id']);
            if ($anggota) {
                if ($data['jenis'] == 'pokok') {
                    $newPokok = $anggota['simpanan_pokok'] + $data['jumlah'];
                    $this->anggotaModel->update($data['anggota_id'], ['simpanan_pokok' => $newPokok]);
                } elseif ($data['jenis'] == 'wajib') {
                    $newWajib = $anggota['simpanan_wajib'] + $data['jumlah'];
                    $this->anggotaModel->update($data['anggota_id'], ['simpanan_wajib' => $newWajib]);
                }
            }
            
            session()->setFlashdata('success', 'Transaksi simpanan berhasil dicatat.');
        } else {
            session()->setFlashdata('error', 'Gagal mencatat transaksi simpanan.');
        }

        return redirect()->to('/dashboard/koperasi/simpanan');
    }

    public function detailSimpanan($id)
    {
        $anggota = $this->anggotaModel->find($id);
        
        if (!$anggota) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Detail Simpanan ' . $anggota['nama'],
            'anggota' => $anggota,
            'simpanan' => $this->simpananModel->getByAnggota($id),
            'total_pokok' => $this->simpananModel->getTotalByJenis($id, 'pokok'),
            'total_wajib' => $this->simpananModel->getTotalByJenis($id, 'wajib'),
            'total_sukarela' => $this->simpananModel->getTotalByJenis($id, 'sukarela')
        ];

        return view('dashboard/koperasi/simpanan/detail', $data);
    }

    public function laporanSimpanan()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        
        $data = [
            'title' => 'Laporan Simpanan',
            'laporan' => $this->simpananModel->getLaporanTahunan($tahun),
            'tahun_list' => $this->simpananModel->getTahunList(),
            'currentTahun' => $tahun,
            'summary' => $this->simpananModel->getSummary($tahun)
        ];

        return view('dashboard/koperasi/simpanan/laporan', $data);
    }

    public function exportSimpanan()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $laporan = $this->simpananModel->getLaporanTahunan($tahun);
        
        $filename = "laporan-simpanan-koperasi-" . $tahun . ".csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, [
            'Bulan', 'Simpanan Pokok', 'Simpanan Wajib', 'Simpanan Sukarela', 'Total'
        ]);
        
        // Data
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        for ($i = 0; $i < 12; $i++) {
            $row = $laporan[$i] ?? ['pokok' => 0, 'wajib' => 0, 'sukarela' => 0];
            $total = $row['pokok'] + $row['wajib'] + $row['sukarela'];
            
            fputcsv($output, [
                $months[$i],
                number_format($row['pokok'], 0, ',', '.'),
                number_format($row['wajib'], 0, ',', '.'),
                number_format($row['sukarela'], 0, ',', '.'),
                number_format($total, 0, ',', '.')
            ]);
        }
        
        fclose($output);
        exit();
    }

    // MANAJEMEN LAPORAN
    public function laporan()
    {
        $data = [
            'title' => 'Manajemen Laporan Koperasi',
            'laporan' => $this->laporanModel->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/laporan/index', $data);
    }

    public function createLaporan()
    {
        $data = [
            'title' => 'Upload Laporan Baru',
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/laporan/form', $data);
    }

    public function storeLaporan()
    {
        $rules = [
            'judul' => 'required|min_length[3]|max_length[200]',
            'jenis' => 'required|in_list[RAT,keuangan,tahunan,bulanan,khusus]',
            'tahun' => 'required|numeric|min_length[4]|max_length[4]',
            'bulan' => 'if_exist|numeric|min_length[1]|max_length[2]',
            'file' => 'uploaded[file]|max_size[file,5120]|ext_in[file,pdf,doc,docx,xls,xlsx]',
            'deskripsi' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file');
        $fileName = null;
        
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/koperasi/laporan/', $newName);
            $fileName = $newName;
        }

        $data = [
            'judul' => $this->request->getPost('judul'),
            'jenis' => $this->request->getPost('jenis'),
            'tahun' => $this->request->getPost('tahun'),
            'bulan' => $this->request->getPost('bulan'),
            'file_path' => $fileName,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->laporanModel->save($data)) {
            session()->setFlashdata('success', 'Laporan berhasil diupload.');
        } else {
            session()->setFlashdata('error', 'Gagal mengupload laporan.');
        }

        return redirect()->to('/dashboard/koperasi/laporan');
    }

    public function editLaporan($id)
    {
        $laporan = $this->laporanModel->find($id);
        
        if (!$laporan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Laporan',
            'laporan' => $laporan,
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/laporan/form', $data);
    }

    public function updateLaporan($id)
    {
        $rules = [
            'judul' => 'required|min_length[3]|max_length[200]',
            'jenis' => 'required|in_list[RAT,keuangan,tahunan,bulanan,khusus]',
            'tahun' => 'required|numeric|min_length[4]|max_length[4]',
            'bulan' => 'if_exist|numeric|min_length[1]|max_length[2]',
            'file' => 'if_exist|uploaded[file]|max_size[file,5120]|ext_in[file,pdf,doc,docx,xls,xlsx]',
            'deskripsi' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'judul' => $this->request->getPost('judul'),
            'jenis' => $this->request->getPost('jenis'),
            'tahun' => $this->request->getPost('tahun'),
            'bulan' => $this->request->getPost('bulan'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Upload file baru jika ada
        $file = $this->request->getFile('file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/koperasi/laporan/', $newName);
            
            // Hapus file lama
            $oldLaporan = $this->laporanModel->find($id);
            if ($oldLaporan['file_path'] && file_exists(ROOTPATH . 'public/uploads/koperasi/laporan/' . $oldLaporan['file_path'])) {
                unlink(ROOTPATH . 'public/uploads/koperasi/laporan/' . $oldLaporan['file_path']);
            }
            
            $data['file_path'] = $newName;
        }

        if ($this->laporanModel->update($id, $data)) {
            session()->setFlashdata('success', 'Laporan berhasil diperbarui.');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui laporan.');
        }

        return redirect()->to('/dashboard/koperasi/laporan');
    }

    public function deleteLaporan($id)
    {
        $laporan = $this->laporanModel->find($id);
        
        if ($laporan) {
            // Hapus file
            if ($laporan['file_path'] && file_exists(ROOTPATH . 'public/uploads/koperasi/laporan/' . $laporan['file_path'])) {
                unlink(ROOTPATH . 'public/uploads/koperasi/laporan/' . $laporan['file_path']);
            }
            
            if ($this->laporanModel->delete($id)) {
                session()->setFlashdata('success', 'Laporan berhasil dihapus.');
            } else {
                session()->setFlashdata('error', 'Gagal menghapus laporan.');
            }
        }

        return redirect()->to('/dashboard/koperasi/laporan');
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

    // MANAJEMEN BERITA
    public function berita()
    {
        $data = [
            'title' => 'Manajemen Berita Koperasi',
            'berita' => $this->beritaModel->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/berita/index', $data);
    }

    public function createBerita()
    {
        $data = [
            'title' => 'Tambah Berita Koperasi',
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/berita/form', $data);
    }

    public function storeBerita()
    {
        $rules = [
            'judul' => 'required|min_length[3]|max_length[200]',
            'konten' => 'required',
            'gambar' => 'uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png]',
            'status' => 'required|in_list[draft,published]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('gambar');
        $fileName = null;
        
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/koperasi/berita/', $newName);
            $fileName = $newName;
        }

        $data = [
            'judul' => $this->request->getPost('judul'),
            'slug' => url_title($this->request->getPost('judul'), '-', true),
            'konten' => $this->request->getPost('konten'),
            'gambar' => $fileName,
            'status' => $this->request->getPost('status'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->beritaModel->save($data)) {
            session()->setFlashdata('success', 'Berita berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan berita.');
        }

        return redirect()->to('/dashboard/koperasi/berita');
    }

    public function editBerita($id)
    {
        $berita = $this->beritaModel->find($id);
        
        if (!$berita) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Berita Koperasi',
            'berita' => $berita,
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/berita/form', $data);
    }

    public function updateBerita($id)
    {
        $rules = [
            'judul' => 'required|min_length[3]|max_length[200]',
            'konten' => 'required',
            'gambar' => 'if_exist|uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png]',
            'status' => 'required|in_list[draft,published]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'judul' => $this->request->getPost('judul'),
            'slug' => url_title($this->request->getPost('judul'), '-', true),
            'konten' => $this->request->getPost('konten'),
            'status' => $this->request->getPost('status'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Upload gambar baru jika ada
        $file = $this->request->getFile('gambar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/koperasi/berita/', $newName);
            
            // Hapus gambar lama
            $oldBerita = $this->beritaModel->find($id);
            if ($oldBerita['gambar'] && file_exists(ROOTPATH . 'public/uploads/koperasi/berita/' . $oldBerita['gambar'])) {
                unlink(ROOTPATH . 'public/uploads/koperasi/berita/' . $oldBerita['gambar']);
            }
            
            $data['gambar'] = $newName;
        }

        if ($this->beritaModel->update($id, $data)) {
            session()->setFlashdata('success', 'Berita berhasil diperbarui.');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui berita.');
        }

        return redirect()->to('/dashboard/koperasi/berita');
    }

    public function deleteBerita($id)
    {
        $berita = $this->beritaModel->find($id);
        
        if ($berita) {
            // Hapus gambar
            if ($berita['gambar'] && file_exists(ROOTPATH . 'public/uploads/koperasi/berita/' . $berita['gambar'])) {
                unlink(ROOTPATH . 'public/uploads/koperasi/berita/' . $berita['gambar']);
            }
            
            if ($this->beritaModel->delete($id)) {
                session()->setFlashdata('success', 'Berita berhasil dihapus.');
            } else {
                session()->setFlashdata('error', 'Gagal menghapus berita.');
            }
        }

        return redirect()->to('/dashboard/koperasi/berita');
    }

    // PENGATURAN
    public function pengaturan()
    {
        $data = [
            'title' => 'Pengaturan Koperasi',
            'settings' => $this->koperasiModel->getAllSettings(),
            'validation' => \Config\Services::validation()
        ];

        return view('dashboard/koperasi/pengaturan/index', $data);
    }

    public function updatePengaturan()
    {
        $data = [
            'nama_koperasi' => $this->request->getPost('nama_koperasi'),
            'singkatan' => $this->request->getPost('singkatan'),
            'alamat' => $this->request->getPost('alamat'),
            'telepon' => $this->request->getPost('telepon'),
            'whatsapp' => $this->request->getPost('whatsapp'),
            'email' => $this->request->getPost('email'),
            'website' => $this->request->getPost('website'),
            'sejarah' => $this->request->getPost('sejarah'),
            'visi' => $this->request->getPost('visi'),
            'misi' => $this->request->getPost('misi'),
            'struktur_organisasi' => $this->request->getPost('struktur_organisasi'),
            'persyaratan_anggota' => $this->request->getPost('persyaratan_anggota'),
            'manfaat_anggota' => $this->request->getPost('manfaat_anggota'),
            'simpanan_pokok' => $this->request->getPost('simpanan_pokok'),
            'simpanan_wajib' => $this->request->getPost('simpanan_wajib'),
            'jam_operasional' => $this->request->getPost('jam_operasional'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->koperasiModel->saveSettings($data)) {
            session()->setFlashdata('success', 'Pengaturan berhasil diperbarui.');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui pengaturan.');
        }

        return redirect()->to('/dashboard/koperasi/pengaturan');
    }

    public function slider()
    {
        $data = [
            'title' => 'Slider Koperasi',
            'sliders' => $this->sliderModel->findAll()
        ];

        return view('dashboard/koperasi/pengaturan/slider', $data);
    }

    public function storeSlider()
    {
        $rules = [
            'judul' => 'required|min_length[3]|max_length[100]',
            'deskripsi' => 'required',
            'gambar' => 'uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png]',
            'link' => 'if_exist|valid_url',
            'status' => 'required|in_list[active,inactive]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('gambar');
        $fileName = null;
        
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/koperasi/slider/', $newName);
            $fileName = $newName;
        }

        $data = [
            'judul' => $this->request->getPost('judul'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'gambar' => $fileName,
            'link' => $this->request->getPost('link'),
            'status' => $this->request->getPost('status'),
            'urutan' => $this->sliderModel->countAll() + 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->sliderModel->save($data)) {
            session()->setFlashdata('success', 'Slider berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan slider.');
        }

        return redirect()->to('/dashboard/koperasi/pengaturan/slider');
    }

    public function deleteSlider($id)
    {
        $slider = $this->sliderModel->find($id);
        
        if ($slider) {
            // Hapus gambar
            if ($slider['gambar'] && file_exists(ROOTPATH . 'public/uploads/koperasi/slider/' . $slider['gambar'])) {
                unlink(ROOTPATH . 'public/uploads/koperasi/slider/' . $slider['gambar']);
            }
            
            if ($this->sliderModel->delete($id)) {
                session()->setFlashdata('success', 'Slider berhasil dihapus.');
            } else {
                session()->setFlashdata('error', 'Gagal menghapus slider.');
            }
        }

        return redirect()->to('/dashboard/koperasi/pengaturan/slider');
    }

    // STATISTIK
    public function statistik()
    {
        $data = [
            'title' => 'Statistik Koperasi',
            'statistik_anggota' => $this->anggotaModel->getStatistik(),
            'statistik_simpanan' => $this->simpananModel->getStatistik(),
            'statistik_unit' => $this->unitUsahaModel->getStatistik(),
            'grafik_anggota' => $this->anggotaModel->getGrafikPendaftaran(),
            'grafik_simpanan' => $this->simpananModel->getGrafikSimpanan(),
            'grafik_unit' => $this->unitUsahaModel->getGrafikPenjualan()
        ];

        return view('dashboard/koperasi/statistik/index', $data);
    }

    public function dashboardData()
    {
        $data = [
            'total_anggota' => $this->anggotaModel->countAll(),
            'total_simpanan' => $this->simpananModel->getTotalSaldo(),
            'total_unit' => $this->unitUsahaModel->countAll(),
            'pendaftaran_pending' => $this->pendaftaranModel->where('status', 'pending')->countAllResults(),
            'grafik_anggota' => $this->anggotaModel->getGrafikPendaftaran(),
            'grafik_simpanan' => $this->simpananModel->getGrafikSimpanan()
        ];

        return $this->response->setJSON($data);
    }
    public function pendaftaran()
    {
        $status = $this->request->getGet('status');
        
        $builder = $this->pendaftaranModel;
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        $data = [
            'title' => 'Manajemen Pendaftaran Anggota',
            'pendaftaran' => $builder->orderBy('created_at', 'DESC')->findAll(),
            'currentStatus' => $status
        ];

        return view('dashboard/koperasi/pendaftaran/index', $data);
    }

    public function detailPendaftaran($id)
    {
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

    public function approvePendaftaran($id)
    {
        $pendaftaran = $this->pendaftaranModel->find($id);
        
        if ($pendaftaran) {
            // Buat data anggota baru
            $anggotaData = [
                'kode_anggota' => 'KA-' . date('Ym') . '-' . str_pad($this->anggotaModel->countAll() + 1, 4, '0', STR_PAD_LEFT),
                'nama' => $pendaftaran['nama'],
                'nik' => $pendaftaran['nik'],
                'tempat_lahir' => $pendaftaran['tempat_lahir'],
                'tanggal_lahir' => $pendaftaran['tanggal_lahir'],
                'jenis_kelamin' => $pendaftaran['jenis_kelamin'],
                'alamat' => $pendaftaran['alamat'],
                'no_hp' => $pendaftaran['no_hp'],
                'email' => $pendaftaran['email'],
                'pekerjaan' => $pendaftaran['pekerjaan'],
                'tanggal_daftar' => date('Y-m-d'),
                'simpanan_pokok' => $pendaftaran['simpanan_pokok'],
                'simpanan_wajib' => $pendaftaran['simpanan_wajib'],
                'status' => 'aktif',
                'foto_ktp' => $pendaftaran['foto_ktp'],
                'foto_diri' => $pendaftaran['foto_diri'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($this->anggotaModel->save($anggotaData)) {
                // Update status pendaftaran
                $this->pendaftaranModel->update($id, [
                    'status' => 'approved',
                    'approved_at' => date('Y-m-d H:i:s')
                ]);

                // Simpan transaksi simpanan pertama
                $simpananData = [
                    'anggota_id' => $this->anggotaModel->getInsertID(),
                    'jenis' => 'pokok',
                    'jumlah' => $pendaftaran['simpanan_pokok'],
                    'tanggal' => date('Y-m-d'),
                    'keterangan' => 'Simpanan Pokok Awal dari Pendaftaran ' . $pendaftaran['kode_pendaftaran'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->simpananModel->save($simpananData);

                session()->setFlashdata('success', 'Pendaftaran berhasil disetujui dan anggota baru telah dibuat.');
            }
        }

        return redirect()->to('/dashboard/koperasi/pendaftaran');
    }

    public function rejectPendaftaran($id)
    {
        $data = [
            'status' => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $this->request->getPost('reason')
        ];

        if ($this->pendaftaranModel->update($id, $data)) {
            session()->setFlashdata('success', 'Pendaftaran berhasil ditolak.');
        }

        return redirect()->to('/dashboard/koperasi/pendaftaran');
    }

    public function deletePendaftaran($id)
    {
        $pendaftaran = $this->pendaftaranModel->find($id);
        
        if ($pendaftaran) {
            // Hapus file foto
            if ($pendaftaran['foto_ktp'] && file_exists(ROOTPATH . 'public/uploads/koperasi/ktp/' . $pendaftaran['foto_ktp'])) {
                unlink(ROOTPATH . 'public/uploads/koperasi/ktp/' . $pendaftaran['foto_ktp']);
            }
            if ($pendaftaran['foto_diri'] && file_exists(ROOTPATH . 'public/uploads/koperasi/foto/' . $pendaftaran['foto_diri'])) {
                unlink(ROOTPATH . 'public/uploads/koperasi/foto/' . $pendaftaran['foto_diri']);
            }
            
            if ($this->pendaftaranModel->delete($id)) {
                session()->setFlashdata('success', 'Pendaftaran berhasil dihapus.');
            }
        }

        return redirect()->to('/dashboard/koperasi/pendaftaran');
    }

   // TAMBAHKAN METHOD INI DI AKHIR CLASS (sebelum closing brace)

public function showAnggota($id)
{
    $anggota = $this->anggotaModel->find($id);
    
    if (!$anggota) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $data = [
        'title' => 'Detail Anggota: ' . $anggota['nama'],
        'anggota' => $anggota
    ];

    return view('dashboard/koperasi/anggota/show', $data);
}

public function showUnit($id)
{
    $unit = $this->unitUsahaModel->find($id);
    
    if (!$unit) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $data = [
        'title' => 'Detail Unit: ' . $unit['nama_unit'],
        'unit' => $unit
    ];

    return view('dashboard/koperasi/unit_usaha/show', $data);
}

public function showBerita($id)
{
    $berita = $this->beritaModel->find($id);
    
    if (!$berita) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $data = [
        'title' => 'Detail Berita: ' . $berita['judul'],
        'berita' => $berita
    ];

    return view('dashboard/koperasi/berita/show', $data);
}
}