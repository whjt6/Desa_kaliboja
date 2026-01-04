<?php

namespace App\Models;

use CodeIgniter\Model;

class PerangkatDesaModel extends Model
{
    protected $table            = 'perangkat_desa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['nip', 'nama', 'jabatan', 'qr_code'];
    protected $useTimestamps    = true;
}