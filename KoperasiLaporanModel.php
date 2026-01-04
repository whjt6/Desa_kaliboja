<?php

namespace App\Models;

use CodeIgniter\Model;

class KoperasiLaporanModel extends Model
{
    protected $table = 'koperasi_laporan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'judul', 'jenis', 'tahun', 'bulan', 'file_path', 'deskripsi'
    ];
    protected $useTimestamps = true;

    public function getLatest($limit = 5)
    {
        return $this->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getByTahun($tahun, $jenis = null)
    {
        $builder = $this->where('tahun', $tahun);
        
        if ($jenis) {
            $builder->where('jenis', $jenis);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function getTahunList()
    {
        $result = $this->select("tahun")
                      ->groupBy("tahun")
                      ->orderBy("tahun", "DESC")
                      ->get()
                      ->getResultArray();
        
        $years = [];
        foreach ($result as $row) {
            $years[] = $row['tahun'];
        }
        
        return $years;
    }
}