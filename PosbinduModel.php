<?php

namespace App\Models;

use CodeIgniter\Model;

class PosbinduModel extends Model
{
    protected $table = 'posbindu_lansia';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'dusun', 'bulan', 'tahun',
        'jumlah_lansia_l', 'jumlah_lansia_p', 'jumlah_lansia_total',
        'tekanan_darah_normal', 'tekanan_darah_tingkat1', 
        'tekanan_darah_tingkat2', 'tekanan_darah_tingkat3',
        'gula_darah_normal', 'gula_darah_pradiabetes', 'gula_darah_diabetes',
        'imt_kurus', 'imt_normal', 'imt_gemuk', 'imt_obesitas',
        'lingkar_perut_normal', 'lingkar_perut_obesitas',
        'urin_normal', 'urin_protein_rendah', 'urin_protein_sedang', 'urin_protein_tinggi',
        'riwayat_hipertensi', 'riwayat_diabetes', 'riwayat_jantung', 'riwayat_stroke',
        'jumlah_rujukan', 'keterangan'
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
            SUM(jumlah_lansia_total) as total_lansia,
            SUM(tekanan_darah_tingkat2 + tekanan_darah_tingkat3) as total_hipertensi,
            SUM(gula_darah_diabetes) as total_diabetes,
            SUM(imt_gemuk + imt_obesitas) as total_gemuk_obesitas,
            SUM(jumlah_rujukan) as total_rujukan
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
            SUM(jumlah_lansia_total) as total_lansia,
            SUM(tekanan_darah_tingkat2 + tekanan_darah_tingkat3) as total_hipertensi,
            SUM(gula_darah_diabetes) as total_diabetes,
            SUM(imt_obesitas) as total_obesitas,
            SUM(jumlah_rujukan) as total_rujukan
        ");
        $builder->where('tahun', $tahun);
        $builder->groupBy('dusun');
        $builder->orderBy('dusun');
        
        return $builder->get()->getResultArray();
    }
    
    public function getRiskDistribution($tahun = null)
    {
        if (!$tahun) {
            $tahun = date('Y');
        }
        
        $builder = $this->db->table($this->table);
        $builder->select("
            SUM(tekanan_darah_normal) as tekanan_normal,
            SUM(tekanan_darah_tingkat1 + tekanan_darah_tingkat2 + tekanan_darah_tingkat3) as tekanan_tinggi,
            SUM(gula_darah_normal) as gula_normal,
            SUM(gula_darah_pradiabetes + gula_darah_diabetes) as gula_tinggi,
            SUM(imt_normal) as imt_normal,
            SUM(imt_kurus + imt_gemuk + imt_obesitas) as imt_tidak_normal
        ");
        $builder->where('tahun', $tahun);
        
        return $builder->get()->getRowArray();
    }
}