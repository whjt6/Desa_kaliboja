<?php

namespace App\Models;

use CodeIgniter\Model;

class KoperasiKepengurusanModel extends Model
{
    protected $table = 'koperasi_kepengurusan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama', 'jabatan', 'kategori', 'urutan', 'foto', 
        'no_hp', 'email', 'status', 'periode_mulai', 'periode_selesai'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';

    /**
     * Get kepengurusan by kategori
     */
    public function getByKategori($kategori = null, $status = 'aktif')
    {
        $builder = $this->where('status', $status);
        
        if ($kategori) {
            $builder->where('kategori', $kategori);
        }
        
        return $builder->orderBy('urutan', 'ASC')
                      ->orderBy('id', 'ASC')
                      ->findAll();
    }

    /**
     * Get all kepengurusan grouped by kategori
     */
    public function getAllGrouped($status = 'aktif')
    {
        $data = $this->where('status', $status)
                    ->orderBy('kategori', 'ASC')
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
        
        $grouped = [
            'pengawas' => [],
            'pengurus' => [],
            'manajemen' => []
        ];
        
        foreach ($data as $item) {
            $grouped[$item['kategori']][] = $item;
        }
        
        return $grouped;
    }

    /**
     * Get kepengurusan aktif saja
     */
    public function getAktif($kategori = null)
    {
        return $this->getByKategori($kategori, 'aktif');
    }

    /**
     * Get total per kategori
     */
    public function getTotalByKategori()
    {
        $result = $this->select('kategori, COUNT(*) as total')
                      ->where('status', 'aktif')
                      ->groupBy('kategori')
                      ->findAll();
        
        $total = [
            'pengawas' => 0,
            'pengurus' => 0,
            'manajemen' => 0
        ];
        
        foreach ($result as $row) {
            $total[$row['kategori']] = (int)$row['total'];
        }
        
        return $total;
    }

    /**
     * Get urutan terakhir untuk kategori
     */
    public function getLastUrutan($kategori)
    {
        $result = $this->where('kategori', $kategori)
                      ->orderBy('urutan', 'DESC')
                      ->first();
        
        return $result ? (int)$result['urutan'] : 0;
    }

    /**
     * Update urutan
     */
    public function updateUrutan($id, $urutan)
    {
        return $this->update($id, ['urutan' => $urutan]);
    }

    /**
     * Reorder kepengurusan dalam kategori
     */
    public function reorder($kategori, $ids)
    {
        $urutan = 1;
        foreach ($ids as $id) {
            $this->update($id, ['urutan' => $urutan]);
            $urutan++;
        }
        return true;
    }

    /**
     * Get untuk dropdown
     */
    public function getForDropdown()
    {
        $data = $this->where('status', 'aktif')
                    ->orderBy('nama', 'ASC')
                    ->findAll();
        
        $dropdown = [];
        foreach ($data as $row) {
            $dropdown[$row['id']] = $row['nama'] . ' - ' . $row['jabatan'];
        }
        
        return $dropdown;
    }

    /**
     * Check if periode overlap
     */
    public function isPeriodeOverlap($jabatan, $periode_mulai, $periode_selesai, $exclude_id = null)
    {
        $builder = $this->where('jabatan', $jabatan)
                       ->where('status', 'aktif')
                       ->groupStart()
                           ->where('periode_mulai <=', $periode_selesai)
                           ->where('periode_selesai >=', $periode_mulai)
                       ->groupEnd();
        
        if ($exclude_id) {
            $builder->where('id !=', $exclude_id);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Get by periode
     */
    public function getByPeriode($tahun)
    {
        return $this->where('periode_mulai <=', $tahun)
                   ->where('periode_selesai >=', $tahun)
                   ->orderBy('kategori', 'ASC')
                   ->orderBy('urutan', 'ASC')
                   ->findAll();
    }
}