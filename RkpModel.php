<?php
namespace App\Models;

use CodeIgniter\Model;

class RkpModel extends Model
{
    protected $table = 'rkp_desa';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tahun', 'nama_kegiatan', 'lokasi', 'volume', 'sasaran',
        'waktu_pelaksanaan', 'jumlah_biaya', 'sumber_dana',
        'pelaksana', 'keterangan', 'status', 'progress', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    
    public function getByTahun($tahun)
    {
        return $this->where('tahun', $tahun)->findAll();
    }
    
    public function getTahunList()
    {
        return $this->select('tahun')->distinct()->orderBy('tahun', 'DESC')->findAll();
    }
    
    public function getStatistikByTahun()
    {
        return $this->select('tahun, COUNT(*) as total_kegiatan, SUM(jumlah_biaya) as total_biaya')
                   ->groupBy('tahun')
                   ->orderBy('tahun', 'DESC')
                   ->findAll();
    }
}