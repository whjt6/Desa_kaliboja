<?php

namespace App\Models;

use CodeIgniter\Model;

class KoperasiBeritaModel extends Model
{
    protected $table = 'koperasi_berita';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'judul', 'slug', 'konten', 'gambar', 'status', 'views'  // Tambahkan 'views'
    ];
    protected $useTimestamps = true;

    public function getLatest($limit = 3)
    {
        return $this->where('status', 'published')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getAll($search = null)
    {
        $builder = $this->where('status', 'published');
        
        if ($search) {
            $builder->groupStart()
                   ->like('judul', $search)
                   ->orLike('konten', $search)
                   ->groupEnd();
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function incrementView($id)
    {
        // Gunakan query builder langsung untuk menghindari error
        return $this->builder()
                    ->where('id', $id)
                    ->set('views', 'views + 1', false)
                    ->update();
    }

    public function getBySlug($slug)
    {
        return $this->where('slug', $slug)
                    ->where('status', 'published')
                    ->first();
    }

    public function getRelatedNews($currentSlug, $limit = 3)
    {
        return $this->where('slug !=', $currentSlug)
                    ->where('status', 'published')
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}