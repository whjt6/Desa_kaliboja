<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class Products extends BaseController
{
    protected $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    // =====================================================================
    // FUNGSI UNTUK HALAMAN PUBLIK
    // =====================================================================

    public function publicIndex()
    {
        helper('text');

        $data = [
            'title'    => 'Produk Desa',
            'products' => $this->productModel->orderBy('created_at', 'DESC')->findAll()
        ];
        return view('pages/products', $data);
    }

    // =====================================================================
    // FUNGSI UNTUK DASHBOARD
    // =====================================================================

    public function index()
    {
        $data = [
            'title'    => 'Manajemen Produk',
            'products' => $this->productModel->orderBy('created_at', 'DESC')->findAll()
        ];
        return view('dashboard/products/index', $data);
    }

    public function create()
    {
        return view('dashboard/products/create', ['title' => 'Tambah Produk']);
    }

    public function store()
    {
        $file = $this->request->getFile('image');
        $fileName = '';
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/products', $fileName);
        }

        $this->productModel->save([
            'name'        => $this->request->getPost('name'),
            'category'    => $this->request->getPost('category'),
            'description' => $this->request->getPost('description'),
            'image'       => $fileName,
            'contact'     => $this->request->getPost('contact'),
            'is_featured' => $this->request->getPost('is_featured') ? 1 : 0,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        // DIUBAH: Mengarahkan kembali ke halaman dashboard produk
        return redirect()->to('dashboard/products')->with('success', 'Produk berhasil ditambahkan');
    }

    public function edit($id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk tidak ditemukan");
        }
        return view('dashboard/products/edit', [
            'title'   => 'Edit Produk',
            'product' => $product
        ]);
    }

    public function update($id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk tidak ditemukan");
        }

        $file = $this->request->getFile('image');
        $fileName = $product['image'];
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/products', $fileName);
        }

        $this->productModel->update($id, [
            'name'        => $this->request->getPost('name'),
         
            'category'    => $this->request->getPost('category'),
            'description' => $this->request->getPost('description'),
            'image'       => $fileName,
            'contact'     => $this->request->getPost('contact'),
            'is_featured' => $this->request->getPost('is_featured') ? 1 : 0,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        // DIUBAH: Mengarahkan kembali ke halaman dashboard produk
        return redirect()->to('dashboard/products')->with('success', 'Produk berhasil diperbarui');
    }

    public function delete($id)
    {
        $this->productModel->delete($id);
        // DIUBAH: Mengarahkan kembali ke halaman dashboard produk
        return redirect()->to('dashboard/products')->with('success', 'Produk berhasil dihapus');
    }
    public function show($id)
{
    $product = $this->productModel->find($id);
    if (!$product) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Produk tidak ditemukan");
    }
    
    $data = [
        'title' => 'Detail Produk - ' . $product['name'],
        'product' => $product
    ];
    
    return view('pages/product_detail', $data);
}
// Di controller manapun

}