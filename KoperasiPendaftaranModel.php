<?php

namespace App\Models;

use CodeIgniter\Model;

class KoperasiPendaftaranModel extends Model
{
    protected $table = 'koperasi_pendaftaran';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_pendaftaran', 'nama', 'nik', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'alamat', 'no_hp', 'email', 'pekerjaan',
        'simpanan_pokok', 'simpanan_wajib', 'foto_ktp', 'foto_diri', 'status',
        'approved_at', 'rejected_at', 'rejection_reason'
    ];
    protected $useTimestamps = true;

    /**
     * Get pendaftaran terbaru
     */
    public function getLatest($limit = 5)
    {
        return $this->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get pendaftaran berdasarkan status
     */
    public function getByStatus($status = null, $limit = null)
    {
        $builder = $this->builder();
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        $builder->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get statistik pendaftaran
     */
    public function getStatistik()
    {
        $result = $this->select("status, COUNT(*) as total")
                      ->groupBy('status')
                      ->get()
                      ->getResultArray();
        
        $statistik = [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0
        ];
        
        foreach ($result as $row) {
            $statistik[$row['status']] = (int)$row['total'];
        }
        
        return $statistik;
    }

    /**
     * Get pending count
     */
    public function getPendingCount()
    {
        return $this->where('status', 'pending')->countAllResults();
    }

    /**
     * Approve pendaftaran
     */
    public function approve($id)
    {
        return $this->update($id, [
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Reject pendaftaran
     */
    public function reject($id, $reason = null)
    {
        return $this->update($id, [
            'status' => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason
        ]);
    }

    /**
     * Generate kode pendaftaran
     */
    public function generateKodePendaftaran()
    {
        $prefix = 'KP-' . date('Ymd') . '-';
        
        $lastPendaftaran = $this->like('kode_pendaftaran', $prefix, 'after')
                               ->orderBy('id', 'DESC')
                               ->first();
        
        if ($lastPendaftaran) {
            $lastNumber = (int)substr($lastPendaftaran['kode_pendaftaran'], -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get grafik pendaftaran
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
     * Get pendaftaran by NIK
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
     * Get daftar tahun pendaftaran
     */
    public function getTahunList()
    {
        $result = $this->select("YEAR(created_at) as tahun")
                      ->groupBy("YEAR(created_at)")
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

    /**
     * Get pendaftaran dengan filter
     */
    public function getAllWithFilter($search = null, $status = null, $tahun = null)
    {
        $builder = $this->builder();
        
        if ($search) {
            $builder->groupStart()
                   ->like('nama', $search)
                   ->orLike('nik', $search)
                   ->orLike('kode_pendaftaran', $search)
                   ->orLike('alamat', $search)
                   ->groupEnd();
        }
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        if ($tahun) {
            $builder->where("YEAR(created_at)", $tahun);
        }
        
        $builder->orderBy('created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Export data pendaftaran
     */
    public function exportData($filters = [])
    {
        $builder = $this->builder();
        
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        if (!empty($filters['start_date'])) {
            $builder->where('created_at >=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $builder->where('created_at <=', $filters['end_date']);
        }
        
        $builder->orderBy('created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }
}