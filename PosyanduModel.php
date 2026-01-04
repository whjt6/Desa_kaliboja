<?php

namespace App\Models;

use CodeIgniter\Model;

class PosyanduModel extends Model
{
    protected $table = 'posyandu';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'dusun', 'bulan', 'tahun',
        'jumlah_balita_l', 'jumlah_balita_p', 
        'balita_gizi_buruk', 'balita_gizi_kurang', 'balita_gizi_baik', 'balita_gizi_lebih',
        'jumlah_ibu_hamil', 'jumlah_ibu_menyusui',
        'kelahiran_l', 'kelahiran_p', 'kelahiran_bb_rendah',
        'imunisasi_dasar_lengkap', 'imunisasi_campak',
        'keterangan'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // Validasi
    protected $validationRules = [
        'dusun' => 'required|in_list[semboja_barat,semboja_timur,kaligenteng,silemud]',
        'bulan' => 'required|valid_date[Y-m]',
        'tahun' => 'required|numeric|min_length[4]|max_length[4]',
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    
    // Nama dusun
    private $dusunNames = [
        'semboja_barat' => 'Semboja Barat',
        'semboja_timur' => 'Semboja Timur',
        'kaligenteng' => 'Kaligenteng',
        'silemud' => 'Silemud'
    ];
    
    public function getDusunName($dusun)
    {
        return $this->dusunNames[$dusun] ?? $dusun;
    }
    
    public function getAllDusunNames()
    {
        return $this->dusunNames;
    }
    
    // Statistik
    public function getTotalStatistik($tahun = null)
    {
        if (!$tahun) {
            $tahun = date('Y');
        }
        
        $builder = $this->db->table($this->table);
        $builder->select("
            SUM(jumlah_balita_l + jumlah_balita_p) as total_balita,
            SUM(jumlah_ibu_hamil) as total_ibu_hamil,
            SUM(jumlah_ibu_menyusui) as total_ibu_menyusui,
            SUM(kelahiran_l + kelahiran_p) as total_kelahiran,
            SUM(imunisasi_dasar_lengkap) as total_imunisasi
        ");
        $builder->where('tahun', $tahun);
        
        return $builder->get()->getRowArray();
    }
    
    public function getStatistikByDusun($tahun = null)
    {
        if (!$tahun) {
            $tahun = date('Y');
        }
        
        $builder = $this->db->table($this->table);
        $builder->select("
            dusun,
            SUM(jumlah_balita_l + jumlah_balita_p) as total_balita,
            SUM(jumlah_ibu_hamil) as total_ibu_hamil,
            SUM(jumlah_ibu_menyusui) as total_ibu_menyusui,
            SUM(kelahiran_l + kelahiran_p) as total_kelahiran,
            SUM(imunisasi_dasar_lengkap) as total_imunisasi
        ");
        $builder->where('tahun', $tahun);
        $builder->groupBy('dusun');
        $builder->orderBy('dusun');
        
        return $builder->get()->getResultArray();
    }
    
    public function getMonthlyData($dusun = null, $tahun = null)
    {
        if (!$tahun) {
            $tahun = date('Y');
        }
        
        $builder = $this->db->table($this->table);
        $builder->where('tahun', $tahun);
        
        if ($dusun) {
            $builder->where('dusun', $dusun);
        }
        
        $builder->orderBy('bulan', 'ASC');
        
        return $builder->get()->getResultArray();
    }
}