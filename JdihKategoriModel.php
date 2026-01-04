<?php
namespace App\Models;

use CodeIgniter\Model;

class JdihKategoriModel extends Model
{
    protected $table = 'jdih_kategori';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama_kategori', 'slug', 'deskripsi', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
}