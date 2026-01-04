<?php

namespace App\Models;

use CodeIgniter\Model;

class KoperasiSimpananModel extends Model
{
    protected $table = 'koperasi_simpanan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_transaksi', 'anggota_id', 'jenis', 'jumlah',
        'tanggal', 'keterangan'
    ];
    protected $useTimestamps = true;

    /**
     * Generate kode transaksi unik
     */
    public function generateKodeTransaksi()
{
    $prefix = 'TRX-' . date('Ymd') . '-';
    
    $lastTransaction = $this->like('kode_transaksi', $prefix, 'after')
                           ->orderBy('id', 'DESC')
                           ->first();
    
    if ($lastTransaction) {
        $lastNumber = (int)substr($lastTransaction['kode_transaksi'], -4);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

    /**
     * Get all simpanan dengan filter
     */
    public function getAllWithFilter($anggota_id = null, $jenis = null, $bulan = null)
    {
        $builder = $this->builder('koperasi_simpanan s')
                       ->select('s.*, a.nama as nama_anggota, a.kode_anggota')
                       ->join('koperasi_anggota a', 'a.id = s.anggota_id');
        
        if ($anggota_id) {
            $builder->where('s.anggota_id', $anggota_id);
        }
        
        if ($jenis) {
            $builder->where('s.jenis', $jenis);
        }
        
        if ($bulan) {
            $builder->where("DATE_FORMAT(s.tanggal, '%Y-%m')", $bulan);
        }
        
        $builder->orderBy('s.tanggal', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get total saldo semua simpanan
     */
    public function getTotalSaldo()
    {
        $result = $this->select("SUM(jumlah) as total")
                      ->get()
                      ->getRowArray();
        
        return $result['total'] ?? 0;
    }

    /**
     * Get simpanan by anggota
     */
    public function getByAnggota($anggota_id)
    {
        return $this->where('anggota_id', $anggota_id)
                   ->orderBy('tanggal', 'DESC')
                   ->findAll();
    }

    /**
     * Get total simpanan by jenis untuk anggota tertentu
     */
    public function getTotalByJenis($anggota_id, $jenis)
    {
        $result = $this->select("SUM(jumlah) as total")
                      ->where('anggota_id', $anggota_id)
                      ->where('jenis', $jenis)
                      ->get()
                      ->getRowArray();
        
        return $result['total'] ?? 0;
    }

    /**
     * Get laporan tahunan per bulan
     */
    public function getLaporanTahunan($tahun)
    {
        $result = $this->select("MONTH(tanggal) as bulan, jenis, SUM(jumlah) as total")
                      ->where("YEAR(tanggal)", $tahun)
                      ->groupBy("MONTH(tanggal), jenis")
                      ->get()
                      ->getResultArray();
        
        // Buat array 12 bulan dengan default 0
        $laporan = array_fill(0, 12, ['pokok' => 0, 'wajib' => 0, 'sukarela' => 0]);
        
        // Isi data dari database
        foreach ($result as $row) {
            $bulan = (int)$row['bulan'] - 1; // Index array mulai dari 0
            $laporan[$bulan][$row['jenis']] = (float)$row['total'];
        }
        
        return $laporan;
    }

    /**
     * Get summary simpanan per tahun
     */
    public function getSummary($tahun)
    {
        $result = $this->select("jenis, SUM(jumlah) as total")
                      ->where("YEAR(tanggal)", $tahun)
                      ->groupBy("jenis")
                      ->get()
                      ->getResultArray();
        
        $summary = ['pokok' => 0, 'wajib' => 0, 'sukarela' => 0];
        
        foreach ($result as $row) {
            $summary[$row['jenis']] = (float)$row['total'];
        }
        
        return $summary;
    }

    /**
     * Get daftar tahun yang ada transaksi
     */
    public function getTahunList()
    {
        $result = $this->select("YEAR(tanggal) as tahun")
                      ->groupBy("YEAR(tanggal)")
                      ->orderBy("tahun", "DESC")
                      ->get()
                      ->getResultArray();
        
        $years = [];
        foreach ($result as $row) {
            $years[] = $row['tahun'];
        }
        
        // Jika kosong, minimal return tahun sekarang
        if (empty($years)) {
            $years[] = date('Y');
        }
        
        return $years;
    }

    /**
     * Get data grafik simpanan (6 bulan terakhir)
     */
    public function getGrafikSimpanan($limit = 6)
    {
        $result = $this->select("DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(jumlah) as total")
                      ->groupBy("DATE_FORMAT(tanggal, '%Y-%m')")
                      ->orderBy('bulan', 'DESC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
        
        return array_reverse($result);
    }

    /**
     * Get statistik simpanan by jenis
     */
    public function getStatistik()
    {
        $result = $this->select("jenis, SUM(jumlah) as total")
                      ->groupBy('jenis')
                      ->get()
                      ->getResultArray();
        
        $statistik = ['pokok' => 0, 'wajib' => 0, 'sukarela' => 0];
        
        foreach ($result as $row) {
            $statistik[$row['jenis']] = (float)$row['total'];
        }
        
        return $statistik;
    }

    /**
     * Get transaksi terbaru
     */
    public function getLatest($limit = 10)
    {
        return $this->select('koperasi_simpanan.*, koperasi_anggota.nama as nama_anggota, koperasi_anggota.kode_anggota')
                   ->join('koperasi_anggota', 'koperasi_anggota.id = koperasi_simpanan.anggota_id')
                   ->orderBy('koperasi_simpanan.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get total simpanan per anggota
     */
    public function getTotalPerAnggota($anggota_id)
    {
        $result = $this->select("
                        SUM(CASE WHEN jenis = 'pokok' THEN jumlah ELSE 0 END) as total_pokok,
                        SUM(CASE WHEN jenis = 'wajib' THEN jumlah ELSE 0 END) as total_wajib,
                        SUM(CASE WHEN jenis = 'sukarela' THEN jumlah ELSE 0 END) as total_sukarela
                    ")
                      ->where('anggota_id', $anggota_id)
                      ->get()
                      ->getRowArray();
        
        return [
            'pokok' => $result['total_pokok'] ?? 0,
            'wajib' => $result['total_wajib'] ?? 0,
            'sukarela' => $result['total_sukarela'] ?? 0,
            'total' => ($result['total_pokok'] ?? 0) + ($result['total_wajib'] ?? 0) + ($result['total_sukarela'] ?? 0)
        ];
    }

    /**
     * Validasi kode transaksi unik
     */
    public function isKodeTransaksiExists($kode_transaksi, $exclude_id = null)
    {
        $builder = $this->where('kode_transaksi', $kode_transaksi);
        
        if ($exclude_id) {
            $builder->where('id !=', $exclude_id);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Export data simpanan ke array (untuk Excel)
     */
    public function exportData($filters = [])
    {
        $builder = $this->builder('koperasi_simpanan s')
                       ->select('s.kode_transaksi, s.tanggal, a.kode_anggota, a.nama as nama_anggota, 
                                s.jenis, s.jumlah, s.keterangan, s.created_at')
                       ->join('koperasi_anggota a', 'a.id = s.anggota_id');
        
        // Apply filters
        if (!empty($filters['anggota_id'])) {
            $builder->where('s.anggota_id', $filters['anggota_id']);
        }
        
        if (!empty($filters['jenis'])) {
            $builder->where('s.jenis', $filters['jenis']);
        }
        
        if (!empty($filters['start_date'])) {
            $builder->where('s.tanggal >=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $builder->where('s.tanggal <=', $filters['end_date']);
        }
        
        $builder->orderBy('s.tanggal', 'DESC');
        
        return $builder->get()->getResultArray();
    }
}