<?php

namespace App\Models;

use CodeIgniter\Model;

class KoperasiSliderModel extends Model
{
    protected $table = 'koperasi_slider';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'judul', 'deskripsi', 'gambar', 'link', 'status', 'urutan'
    ];
    protected $useTimestamps = true;

    public function getActiveSliders()
    {
        return $this->where('status', 'active')
                   ->orderBy('urutan', 'ASC')
                   ->findAll();
    }
}