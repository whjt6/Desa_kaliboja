<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GalleryModel;

class Gallery extends BaseController
{
    protected $galleryModel;

    public function __construct()
    {
        $this->galleryModel = new GalleryModel();
    }

    // ==================== METHOD PUBLIK ====================
    public function index()
    {
        $data = [
            'title' => 'Galeri Desa Kaliboja',
            'galleries' => $this->galleryModel->orderBy('created_at', 'DESC')->findAll()
        ];
        
        return view('gallery/index', $data);
    }

    // ALIAS untuk publicIndex (untuk kompatibilitas dengan route lama)
    public function publicIndex()
    {
        return $this->index();
    }

    public function detail($id)
    {
        $gallery = $this->galleryModel->find($id);
        
        if (!$gallery) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => $gallery['title'],
            'gallery' => $gallery
        ];
        
        return view('gallery/detail', $data);
    }

    // ==================== METHOD ADMIN ====================
    public function adminIndex()
    {
        $data = [
            'title' => 'Manajemen Galeri',
            'galleries' => $this->galleryModel->orderBy('created_at', 'DESC')->findAll()
        ];
        
        return view('dashboard/gallery/index', $data);
    }

    // ... (method create, store, edit, update, delete tetap sama) ...


    public function create()
    {
        $data = [
            'title' => 'Tambah Foto Galeri'
        ];
        return view('dashboard/gallery/create', $data);
    }

    public function store()
    {
        // Validasi
        $validation = \Config\Services::validation();
        $validation->setRules([
            'title' => 'required|min_length[3]|max_length[255]',
            'image' => 'uploaded[image]|max_size[image,2048]|is_image[image]',
            'description' => 'max_length[500]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Upload gambar
        $image = $this->request->getFile('image');
        $newName = $image->getRandomName();
        $image->move(ROOTPATH . 'public/uploads/gallery', $newName);

        // Simpan data
        $this->galleryModel->save([
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'image' => $newName,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/dashboard/gallery')->with('success', 'Foto berhasil ditambahkan');
    }

    public function edit($id)
    {
        $gallery = $this->galleryModel->find($id);
        
        if (!$gallery) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Foto Galeri',
            'gallery' => $gallery
        ];
        
        return view('dashboard/gallery/edit', $data);
    }

    public function update($id)
    {
        $gallery = $this->galleryModel->find($id);
        
        if (!$gallery) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Validasi
        $validation = \Config\Services::validation();
        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'description' => 'max_length[500]'
        ];

        if ($this->request->getFile('image')->isValid()) {
            $rules['image'] = 'uploaded[image]|max_size[image,2048]|is_image[image]';
        }

        $validation->setRules($rules);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Upload gambar baru jika ada
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            // Hapus gambar lama
            if ($gallery['image'] && file_exists(ROOTPATH . 'public/uploads/gallery/' . $gallery['image'])) {
                unlink(ROOTPATH . 'public/uploads/gallery/' . $gallery['image']);
            }
            
            $newName = $image->getRandomName();
            $image->move(ROOTPATH . 'public/uploads/gallery', $newName);
            $data['image'] = $newName;
        }

        $this->galleryModel->update($id, $data);

        return redirect()->to('/dashboard/gallery')->with('success', 'Foto berhasil diperbarui');
    }

    public function delete($id)
    {
        $gallery = $this->galleryModel->find($id);
        
        if (!$gallery) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Hapus gambar dari server
        if ($gallery['image'] && file_exists(ROOTPATH . 'public/uploads/gallery/' . $gallery['image'])) {
            unlink(ROOTPATH . 'public/uploads/gallery/' . $gallery['image']);
        }

        $this->galleryModel->delete($id);

        return redirect()->to('/dashboard/gallery')->with('success', 'Foto berhasil dihapus');
    }
}