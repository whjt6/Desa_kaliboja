<?php
namespace App\Controllers;

use App\Models\JdihKategoriModel;
use App\Models\JdihPeraturanModel;

class JdihFrontController extends BaseController
{
    protected $kategoriModel;
    protected $peraturanModel;

    public function __construct()
    {
        $this->kategoriModel = new JdihKategoriModel();
        $this->peraturanModel = new JdihPeraturanModel();
    }

    public function index()
    {
        $data = [
            'title' => 'JDIH - Jaringan Dokumentasi dan Informasi Hukum',
            'peraturans' => $this->peraturanModel->getPeraturanWithKategori(),
            'kategories' => $this->kategoriModel->findAll(),
            'tahun' => $this->peraturanModel->select('tahun')->distinct()->orderBy('tahun', 'DESC')->findAll()
        ];
        
        return view('jdih/index', $data);
    }

    public function kategori($id)
    {
        $kategori = $this->kategoriModel->find($id);
        $data = [
            'title' => 'Kategori: ' . $kategori['nama_kategori'],
            'peraturans' => $this->peraturanModel->getByKategori($id),
            'kategori' => $kategori,
            'kategories' => $this->kategoriModel->findAll()
        ];
        
        return view('jdih/kategori', $data);
    }

    public function detail($id)
    {
        $peraturan = $this->peraturanModel->select('jdih_peraturan.*, jdih_kategori.nama_kategori')
                                        ->join('jdih_kategori', 'jdih_kategori.id = jdih_peraturan.kategori_id')
                                        ->where('jdih_peraturan.id', $id)
                                        ->first();

        if (!$peraturan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => $peraturan['jenis_peraturan'] . ' No. ' . $peraturan['nomor'] . ' Tahun ' . $peraturan['tahun'],
            'peraturan' => $peraturan
        ];
        
        return view('jdih/detail', $data);
    }

    public function search()
    {
        $keyword = $this->request->getGet('q');
        $data = [
            'title' => 'Pencarian: ' . $keyword,
            'peraturans' => $this->peraturanModel->select('jdih_peraturan.*, jdih_kategori.nama_kategori')
                                               ->join('jdih_kategori', 'jdih_kategori.id = jdih_peraturan.kategori_id')
                                               ->like('jdih_peraturan.tentang', $keyword)
                                               ->orLike('jdih_peraturan.nomor', $keyword)
                                               ->orLike('jdih_peraturan.abstrak', $keyword)
                                               ->findAll(),
            'keyword' => $keyword,
            'kategories' => $this->kategoriModel->findAll()
        ];
        
        return view('jdih/search', $data);
    }

    public function download($id)
    {
        $peraturan = $this->peraturanModel->find($id);
        
        if (!$peraturan || !$peraturan['file_dokumen']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $filePath = 'uploads/jdih/' . $peraturan['file_dokumen'];
        
        if (!file_exists($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response->download($filePath, null);
    }

    public function tahun($tahun)
    {
        $data = [
            'title' => 'Peraturan Tahun ' . $tahun,
            'peraturans' => $this->peraturanModel->select('jdih_peraturan.*, jdih_kategori.nama_kategori')
                                               ->join('jdih_kategori', 'jdih_kategori.id = jdih_peraturan.kategori_id')
                                               ->where('jdih_peraturan.tahun', $tahun)
                                               ->findAll(),
            'tahun' => $tahun,
            'kategories' => $this->kategoriModel->findAll()
        ];
        
        return view('jdih/tahun', $data);
    }
}