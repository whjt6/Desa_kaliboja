<?php

namespace App\Models;

use CodeIgniter\Model;

class WisataModel extends Model
{
    protected $table            = 'wisata';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['nama', 'lokasi', 'deskripsi', 'tiket', 'gambar'];
    protected $useTimestamps    = true;
}
