<?php
namespace App\Models;

use CodeIgniter\Model;

class GalleryModel extends Model
{
    protected $table = 'gallery';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'description', 'image', 'created_at'];
    protected $useTimestamps = false;
}