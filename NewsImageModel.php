<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsImageModel extends Model
{
    protected $table            = 'news_images';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['news_id', 'image_filename', 'uploaded_at'];
    
    // Kita tidak menggunakan created_at/updated_at bawaan CI
    protected $useTimestamps = false; 
}