<?php

namespace App\Models;

use CodeIgniter\Model;

class PopulationModel extends Model
{
    protected $table            = 'populations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'NO', 'RW', 'RT', 'NO_RUMAH', 'NO_KK', 'NIK', 'NAMA', 'JK', 'TMPT_LHR',
        'TGL_LHR', 'AGAMA', 'STATUS', 'SHDK', 'PDDK_AKHIR', 'PEKERJAAN',
        'NAMA_AYAH', 'NAMA_IBU', 'T', 'B', 'ALAMAT'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getStats()
    {
        try {
            $query = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN JK = 'L' THEN 1 ELSE 0 END) as laki_laki,
                    SUM(CASE WHEN JK = 'P' THEN 1 ELSE 0 END) as perempuan
                FROM {$this->table}
            ");
            return $query->getRowArray() ?: ['total' => 0, 'laki_laki' => 0, 'perempuan' => 0];
        } catch (\Exception $e) {
            log_message('error', 'Error fetching population stats: ' . $e->getMessage());
            return ['total' => 0, 'laki_laki' => 0, 'perempuan' => 0];
        }
    }

    public function getGenderStats()
    {
        try {
            return $this->select('JK as gender, COUNT(id) as total')
                        ->groupBy('JK')
                        ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Error fetching gender stats: ' . $e->getMessage());
            return [];
        }
    }
}