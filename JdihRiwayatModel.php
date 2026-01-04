<?php
namespace App\Models;

use CodeIgniter\Model;

class JdihRiwayatModel extends Model
{
    protected $table = 'jdih_riwayat';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'peraturan_id', 'status', 'tanggal_status', 'keterangan'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';

    public function getRiwayatByPeraturan($peraturan_id)
    {
        return $this->where('peraturan_id', $peraturan_id)
                    ->orderBy('tanggal_status', 'DESC')
                    ->findAll();
    }
}