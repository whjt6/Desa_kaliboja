<?php
namespace App\Models;

use CodeIgniter\Model;

class JdihPeraturanModel extends Model
{
    protected $table = 'jdih_peraturan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kategori_id', 'jenis_peraturan', 'nomor', 'tahun', 'tentang', 
        'tanggal_ditetapkan', 'tanggal_diundangkan', 'file_dokumen',
        'abstrak', 'status', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    
    public function getPeraturanWithKategori()
    {
        return $this->select('jdih_peraturan.*, jdih_kategori.nama_kategori')
                   ->join('jdih_kategori', 'jdih_kategori.id = jdih_peraturan.kategori_id')
                   ->orderBy('jdih_peraturan.created_at', 'DESC')
                   ->findAll();
    }
    
    public function getByKategori($kategori_id)
    {
        return $this->select('jdih_peraturan.*, jdih_kategori.nama_kategori')
                   ->join('jdih_kategori', 'jdih_kategori.id = jdih_peraturan.kategori_id')
                   ->where('jdih_peraturan.kategori_id', $kategori_id)
                   ->orderBy('jdih_peraturan.created_at', 'DESC')
                   ->findAll();
    }
}