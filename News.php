<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NewsModel;
use App\Models\NewsImageModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class News extends BaseController
{
    protected $newsModel;
    protected $newsImageModel;

    public function __construct()
    {
        $this->newsModel = new NewsModel();
        $this->newsImageModel = new NewsImageModel();
    }

    /**
     * Menampilkan daftar semua berita di dashboard.
     */
    public function index()
    {
        $newsData = $this->newsModel->orderBy('created_at', 'DESC')->findAll();

        if (!empty($newsData)) {
            foreach ($newsData as $key => $news) {
                if (isset($news['id'])) {
                    $newsData[$key]['images'] = $this->newsImageModel
                        ->where('news_id', $news['id'])
                        ->findAll();
                } else {
                    $newsData[$key]['images'] = [];
                }
            }
        }

        $data = [
            'title' => 'Manajemen Berita',
            'news'  => $newsData
        ];

        return view('news/index', $data);
    }

    /**
     * Menampilkan form untuk membuat berita baru.
     */
    public function create()
    {
        $data = [
            'title' => 'Tambah Berita Baru'
        ];
        return view('news/create', $data);
    }

    /**
     * Menyimpan berita baru ke database.
     */
    public function store()
    {
        $rules = [
            'title'    => 'required|min_length[5]',
            'content'  => 'required',
            'images.*' => 'uploaded[images]|is_image[images]|mime_in[images,image/jpg,image/jpeg,image/png,image/gif]|max_size[images,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newsId = $this->newsModel->insert([
            'title'   => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
        ]);

        $imageFiles = $this->request->getFiles();

        if (isset($imageFiles['images'])) {
            foreach ($imageFiles['images'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newFileName = $file->getRandomName();
                    $file->move(FCPATH . 'uploads/news', $newFileName);

                    $this->newsImageModel->insert([
                        'news_id'        => $newsId,
                        'image_filename' => $newFileName,
                    ]);
                }
            }
        }

        return redirect()->to('/dashboard/news')->with('message', 'Berita baru berhasil dipublikasikan!');
    }
    
    /**
     * Menampilkan form untuk mengedit berita.
     */
    public function edit($id)
    {
        $newsData = $this->newsModel->find($id);

        if (!$newsData) {
            throw new PageNotFoundException('Berita dengan ID ' . $id . ' tidak ditemukan.');
        }

        $newsData['images'] = $this->newsImageModel->where('news_id', $id)->findAll();

        $data = [
            'title' => 'Edit Berita',
            'news'  => $newsData,
        ];

        return view('news/edit', $data);
    }

    /**
     * Memperbarui data berita di database.
     */
    public function update($id)
    {
        $rules = [
            'title'    => 'required|min_length[5]',
            'content'  => 'required',
            'images.*' => 'is_image[images]|mime_in[images,image/jpg,image/jpeg,image/png,image/gif]|max_size[images,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->newsModel->update($id, [
            'title'   => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
        ]);

        $imagesToDelete = $this->request->getPost('delete_images');
        if (!empty($imagesToDelete)) {
            foreach ($imagesToDelete as $imageId) {
                $image = $this->newsImageModel->find($imageId);
                if ($image) {
                    $filePath = FCPATH . 'uploads/news/' . $image['image_filename'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $this->newsImageModel->delete($imageId);
                }
            }
        }

        $imageFiles = $this->request->getFiles();
        if (isset($imageFiles['images']) && $imageFiles['images'][0]->isValid()) {
            foreach ($imageFiles['images'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move(FCPATH . 'uploads/news', $newName);
                    $this->newsImageModel->insert([
                        'news_id'        => $id,
                        'image_filename' => $newName,
                    ]);
                }
            }
        }

        return redirect()->to('/dashboard/news')->with('message', 'Berita berhasil diperbarui!');
    }

    /**
     * Menampilkan detail satu berita.
     */
    public function show($id)
    {
        $newsData = $this->newsModel->find($id);

        if (!$newsData) {
            throw new PageNotFoundException('Berita dengan ID ' . $id . ' tidak ditemukan.');
        }

        $newsData['images'] = $this->newsImageModel->where('news_id', $id)->findAll();

        $data = [
            'title' => $newsData['title'],
            'news'  => $newsData,
        ];

        return view('news/show', $data);
    }

    /**
     * Menghapus berita dan semua gambar terkait.
     */
    public function delete($id)
    {
        $news = $this->newsModel->find($id);
        if (!$news) {
            return redirect()->to('/dashboard/news')->with('error', 'Berita tidak ditemukan.');
        }

        $images = $this->newsImageModel->where('news_id', $id)->findAll();
        if (!empty($images)) {
            foreach ($images as $image) {
                $filePath = FCPATH . 'uploads/news/' . $image['image_filename'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
        
        $this->newsModel->delete($id);

        return redirect()->to('/dashboard/news')->with('message', 'Berita berhasil dihapus!');
    }
    // Di controller manapun

}
