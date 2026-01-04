<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table            = 'news';
    protected $primaryKey       = 'id';
    // Tambahkan 'updated_at' ke dalam allowedFields
    protected $allowedFields    = ['title', 'content', 'created_at', 'updated_at'];

    // Pastikan ini bernilai true
    protected $useTimestamps = true;
}