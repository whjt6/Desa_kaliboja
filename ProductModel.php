<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    // field yang bisa diisi
    protected $allowedFields    = [
        'name',
        'category',
        'description',
        'image',
        'contact',
        'is_featured',
        'created_at',
        'updated_at'
    ];
}
