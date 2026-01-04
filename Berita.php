<?php

namespace App\Controllers;

use App\Models\NewsModel;
use App\Models\NewsImageModel;

class Berita extends BaseController
{
    public function index()
    {
        $newsModel = new NewsModel();
        $newsImageModel = new NewsImageModel();
        
        // Ambil semua berita
        $news = $newsModel->orderBy('created_at', 'DESC')->findAll();
        
        // Ambil gambar untuk setiap berita
        foreach ($news as &$item) {
            $images = $newsImageModel->where('news_id', $item['id'])->findAll();
            $item['images'] = $images;
        }
        
        $data = [
            'title' => 'Berita Desa Kaliboja',
            'news' => $news
        ];
        
        return view('berita/index', $data);
    }
    
    public function detail($id)
    {
        $newsModel = new NewsModel();
        $newsImageModel = new NewsImageModel();
        
        $news = $newsModel->find($id);
        
        if (!$news) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        $images = $newsImageModel->where('news_id', $id)->findAll();
        
        $data = [
            'title' => $news['title'] . ' - Berita Desa Kaliboja',
            'news' => $news,
            'images' => $images
        ];
        
        return view('berita/detail', $data);
    }
}