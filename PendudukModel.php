<?php

namespace App\Models;

use CodeIgniter\Model;

class PendudukModel extends Model
{
    // Sesuaikan dengan nama tabel penduduk Anda
    protected $table            = 'penduduk'; 
    protected $primaryKey       = 'id';

    // Sesuaikan field ini dengan struktur tabel Anda
    protected $allowedFields    = ['nik', 'no_kk', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'alamat'];

    protected $useTimestamps = true;
}