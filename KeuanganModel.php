<?php
namespace App\Models;

use CodeIgniter\Model;

class KeuanganModel extends Model
{
    protected $table      = 'keuangan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tanggal', 'keterangan', 'pemasukan', 'pengeluaran'];
    protected $useTimestamps = true; // otomatis isi created_at & updated_at
}
