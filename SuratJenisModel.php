<?php
namespace App\Models;
use CodeIgniter\Model;

class SuratJenisModel extends Model
{
    protected $table            = 'surat_jenis';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['kode_surat', 'nama_surat', 'deskripsi', 'fields'];
}