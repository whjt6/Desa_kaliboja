<?php

namespace App\Models;

use CodeIgniter\Model;

class KoperasiAnggotaModel extends Model
{
    protected $table = 'koperasi_anggota';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_anggota', 'nama', 'nik', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'alamat', 'no_hp', 'email', 'pekerjaan',
        'tanggal_daftar', 'simpanan_pokok', 'simpanan_wajib', 'status',
        'foto_ktp', 'foto_diri'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';

    /**
     * Get all anggota dengan filter
     */
    public function getAllWithFilter($search = null, $status = null)
    {
        $builder = $this->builder();
        
        if ($search) {
            $builder->groupStart()
                   ->like('nama', $search)
                   ->orLike('nik', $search)
                   ->orLike('kode_anggota', $search)
                   ->orLike('alamat', $search)
                   ->groupEnd();
        }
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        $builder->orderBy('created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get statistik anggota
     */
    public function getStatistik()
    {
        $result = $this->select("status, COUNT(*) as total")
                      ->groupBy('status')
                      ->get()
                      ->getResultArray();
        
        $statistik = [
            'aktif' => 0,
            'nonaktif' => 0,
            'keluar' => 0
        ];
        
        foreach ($result as $row) {
            $statistik[$row['status']] = (int)$row['total'];
        }
        
        return $statistik;
    }

    /**
     * Get grafik pendaftaran anggota
     */
    public function getGrafikPendaftaran($limit = 12)
    {
        $result = $this->select("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total")
                      ->groupBy("DATE_FORMAT(created_at, '%Y-%m')")
                      ->orderBy('bulan', 'DESC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
        
        return array_reverse($result);
    }

    /**
     * Get total saldo simpanan semua anggota aktif
     */
    public function getTotalSaldo()
    {
        $result = $this->select("SUM(simpanan_pokok + simpanan_wajib) as total")
                      ->where('status', 'aktif')
                      ->get()
                      ->getRowArray();
        
        return $result['total'] ?? 0;
    }

    /**
     * Get anggota by kode
     */
    public function getByKode($kode_anggota)
    {
        return $this->where('kode_anggota', $kode_anggota)->first();
    }

    /**
     * Get anggota by NIK
     */
    public function getByNIK($nik)
    {
        return $this->where('nik', $nik)->first();
    }

    /**
     * Check if NIK exists
     */
    public function isNIKExists($nik, $exclude_id = null)
    {
        $builder = $this->where('nik', $nik);
        
        if ($exclude_id) {
            $builder->where('id !=', $exclude_id);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Generate kode anggota
     */
    public function generateKodeAnggota()
    {
        $prefix = 'KA-' . date('Ym') . '-';
        
        $lastAnggota = $this->like('kode_anggota', $prefix, 'after')
                           ->orderBy('id', 'DESC')
                           ->first();
        
        if ($lastAnggota) {
            $lastNumber = (int)substr($lastAnggota['kode_anggota'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get anggota aktif
     */
    public function getAktif($limit = null)
    {
        $builder = $this->where('status', 'aktif')
                       ->orderBy('nama', 'ASC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->findAll();
    }

    /**
     * Get anggota for dropdown
     */
    public function getForDropdown()
    {
        $anggota = $this->where('status', 'aktif')
                       ->orderBy('nama', 'ASC')
                       ->findAll();
        
        $dropdown = [];
        foreach ($anggota as $row) {
            $dropdown[$row['id']] = $row['kode_anggota'] . ' - ' . $row['nama'];
        }
        
        return $dropdown;
    }

    /**
     * Get statistik jenis kelamin
     */
    public function getStatistikJenisKelamin()
    {
        $result = $this->select("jenis_kelamin, COUNT(*) as total")
                      ->where('status', 'aktif')
                      ->groupBy('jenis_kelamin')
                      ->get()
                      ->getResultArray();
        
        $statistik = [
            'L' => 0,
            'P' => 0
        ];
        
        foreach ($result as $row) {
            $statistik[$row['jenis_kelamin']] = (int)$row['total'];
        }
        
        return $statistik;
    }

    /**
     * Get anggota terbaru
     */
    public function getLatest($limit = 10)
    {
        return $this->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get ulang tahun bulan ini
     */
    public function getUlangTahunBulanIni()
    {
        $bulan = date('m');
        
        return $this->where("MONTH(tanggal_lahir)", $bulan)
                   ->where('status', 'aktif')
                   ->orderBy("DAY(tanggal_lahir)", 'ASC')
                   ->findAll();
    }

    /**
     * Get statistik usia
     */
    public function getStatistikUsia()
    {
        $result = $this->select("
                        CASE
                            WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 20 THEN '< 20'
                            WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 20 AND 30 THEN '20-30'
                            WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 31 AND 40 THEN '31-40'
                            WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 41 AND 50 THEN '41-50'
                            ELSE '> 50'
                        END as range_usia,
                        COUNT(*) as total
                    ")
                      ->where('status', 'aktif')
                      ->groupBy('range_usia')
                      ->get()
                      ->getResultArray();
        
        return $result;
    }

    /**
     * Get total anggota by tahun
     */
    public function getTotalByTahun($tahun)
    {
        return $this->where("YEAR(tanggal_daftar)", $tahun)
                   ->countAllResults();
    }

    /**
     * Export data anggota
     */
    public function exportData($filters = [])
    {
        $builder = $this->builder();
        
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        if (!empty($filters['start_date'])) {
            $builder->where('tanggal_daftar >=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $builder->where('tanggal_daftar <=', $filters['end_date']);
        }
        
        $builder->orderBy('tanggal_daftar', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get anggota dengan simpanan
     */
    public function getWithSimpanan($id)
    {
        $anggota = $this->find($id);
        
        if ($anggota) {
            // Load simpanan model
            $simpananModel = new \App\Models\KoperasiSimpananModel();
            $anggota['simpanan'] = $simpananModel->where('anggota_id', $id)->findAll();
            $anggota['total_simpanan'] = $simpananModel->getTotalPerAnggota($id);
        }
        
        return $anggota;
    }

    /**
     * Update status anggota
     */
    public function updateStatus($id, $status)
    {
        return $this->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get daftar tahun pendaftaran
     */
    public function getTahunList()
    {
        $result = $this->select("YEAR(tanggal_daftar) as tahun")
                      ->groupBy("YEAR(tanggal_daftar)")
                      ->orderBy("tahun", "DESC")
                      ->get()
                      ->getResultArray();
        
        $years = [];
        foreach ($result as $row) {
            $years[] = $row['tahun'];
        }
        
        if (empty($years)) {
            $years[] = date('Y');
        }
        
        return $years;
    }
}