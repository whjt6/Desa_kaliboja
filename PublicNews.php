<?php

namespace App\Controllers;

use App\Models\NewsModel;

class PublicNews extends BaseController
{
    protected $newsModel;

    public function __construct()
    {
        // Pastikan Anda memanggil model yang benar
        $this->newsModel = new NewsModel();
    }

    public function index()
    {
        // ... (kode untuk daftar berita, jika ada)
    }

    public function detail($id)
    {
        // 1. Ambil data berita utama terlebih dahulu
        $news = $this->newsModel->find($id);

        if (!$news) {
            // Tampilkan halaman 404 jika berita tidak ditemukan
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Berita tidak ditemukan.");
        }

        // 2. Ambil SEMUA gambar yang terkait dengan berita ini dari tabel news_images
        $db = \Config\Database::connect();
        $images = $db->table('news_images')
                       ->where('news_id', $id)
                       ->get()
                       ->getResultArray(); // Mengambil semua hasil sebagai array

        // 3. Kirim data berita dan semua gambarnya ke view
        $data = [
            'title'  => $news['title'],
            'news'   => $news,
            'images' => $images, // Ini adalah array yang berisi semua gambar
        ];

        return view('pages/detail_berita', $data);
    }
    // Di controller manapun

}
