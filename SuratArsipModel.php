<?php
namespace App\Models;
use CodeIgniter\Model;

class SuratArsipModel extends Model
{
    protected $table            = 'surat_arsip';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['surat_jenis_id', 'nomor_surat', 'tanggal_surat', 'data_pemohon', 'file_path', 'created_at'];
}